<?php

namespace app\controllers;

use app\components\feed\Feed;
use app\components\feed\Item;

use app\components\UserPermissions;
use app\models\Comment;
use app\models\Image;
use app\models\ImageUploadForm;
use app\models\Project;
use app\models\ProjectFilterForm;
use app\models\Tag;
use app\models\User;
use app\models\Vote;
use app\notifier\NewProjectNotification;
use app\notifier\Notifier;
use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Markdown;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

class ProjectController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['create', 'update', 'delete-image', 'bookmarks', 'delete', 'delete-comment'], //only be applied to
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create', 'update', 'delete-image', 'bookmarks', 'publish', 'draft', 'delete', 'delete-comment'],
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verb' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'publish' => ['post'],
                    'draft' => ['post'],
                    'delete' => ['post'],
                    'delete-comment' => ['post'],
                ]
            ]
        ];
    }

    public function actionIndex()
    {
        $limit = Yii::$app->params['project.pageSize'];

        $featuredProvider = new ActiveDataProvider([
            'pagination' => [
                'defaultPageSize' => Yii::$app->params['project.pageSize'],
            ],
            'query' => Project::find()
                ->with('images')
                ->with('tags')
                ->featured()
                ->publishedOrEditable()
                ->freshFirst()
                ->limit($limit)
        ]);

        return $this->render('index', [
            'featuredProvider' => $featuredProvider,
        ]);
    }

    public function actionList()
    {
        $filterForm = new ProjectFilterForm();
        $filterForm->load(Yii::$app->request->get());

        $tagsDataProvider = new ActiveDataProvider([
            'query' => Tag::find()->top(10),
            'pagination' => false,
        ]);

        return $this->render('list', [
            'dataProvider' => $filterForm->getDataProvider(),
            'tagsDataProvider' => $tagsDataProvider,
            'filterForm' => $filterForm,
        ]);
    }

    /**
     * Return Top projects.
     *
     * @return string
     */
    public function actionTopProjects()
    {
        $maxTopProjects = Yii::$app->params['project.maxTopProjects'];
        
        $dataProvider = new ActiveDataProvider([
            'pagination' => false,
            'query' => Project::find()
                ->with('images')
                ->with('tags')
                ->published()
                ->innerJoin([
                    'v' => Vote::find()
                        ->select([
                            'project_id', 
                            'sumValue' => new Expression('SUM(value)'), 
                            'countVote' => new Expression('COUNT(*)')
                        ])
                        ->groupBy('project_id')
                        ->having('sumValue >= 0')
                ], 'v.project_id = project.id')
                ->orderBy([
                    'v.sumValue' => SORT_DESC,
                    'v.countVote' => SORT_DESC,
                ])
                ->limit($maxTopProjects)
        ]); 

        return $this->render('topProjects', [
            'dataProvider' => $dataProvider,
            'maxTopProjects' => $maxTopProjects
        ]);
    }
    
    /**
     * Return bookmark projects.
     * 
     * @return string
     */
    public function actionBookmarks()
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        
        $dataProvider = new ActiveDataProvider([
            'query' => $user->getBookmarkedProjects(),
        ]);
        
        return $this->render('bookmarks', [
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionCreate()
    {
        $model = new Project();
        if (UserPermissions::canManageProjects()) {
            $model->setScenario(Project::SCENARIO_MANAGE);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $notifier = new Notifier(new NewProjectNotification($model));
            $notifier->sendEmails();
            $model->refresh();
            return $this->redirect(['screenshots', 'uuid' => $model->uuid]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionRss()
    {
        /** @var Project[] $projects */
        $projects = Project::find()
            ->with('images', 'users')
            ->published()
            ->freshFirst()
            ->limit(50)
            ->all();

        $feed = new Feed();
        $feed->title = Yii::$app->params['siteName'];
        $feed->link = Url::to('/', true);
        $feed->selfLink = Url::to(['/rss'], true);
        $feed->description =  Yii::$app->params['description'];
        $feed->language = 'zh-CN';
        $feed->setWebMaster(Yii::$app->params['adminEmail'], Yii::$app->params['siteName']);
        $feed->setManagingEditor(Yii::$app->params['adminEmail'], Yii::$app->params['siteName']);

        foreach ($projects as $project) {
            $url = Url::to(['project/view', 'uuid' => $project->uuid, 'slug' => $project->slug], true);
            $item = new Item();
            $item->title = $project->title;
            $item->link = $url;
            $item->guid = $url;

            $imageTag = '';

            if (!empty($project->images)) {
                $imageTag = Html::img($project->images[0]->getThumbnailAbsoluteUrl()) . '<br>';
            }

            $item->description = $imageTag . HtmlPurifier::process(Markdown::process($project->getDescription()), Yii::$app->params['HtmlPurifier.projectDescription']);

            if (!empty($project->link)) {
                $item->description .= Html::a(Html::encode($project->url), $project->url);
            }

            $item->pubDate = $project->created_at;
            $authors = [];
            foreach ($project->users as $user) {
                $authors[] = '@' . $user->username;
            }

            $item->setAuthor(Yii::$app->params['adminEmail'], implode(', ', $authors));
            $feed->addItem($item);
        }

        $feed->render();
    }

    /**
     * @param string $uuid
     * @return string|Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionUpdate($uuid)
    {
        $model = $this->findModel(['uuid' => $uuid]);

        if (!UserPermissions::canManageProject($model)) {
            throw new ForbiddenHttpException(Yii::t('project', 'You can not update this project.'));
        }

        if (Yii::$app->user->can(UserPermissions::MANAGE_PROJECTS)) {
            $model->setScenario(Project::SCENARIO_MANAGE);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['screenshots', 'uuid' => $model->uuid]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * @param string $uuid
     * @return string|Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionScreenshots($uuid)
    {
        $model = $this->findModel(['uuid' => $uuid]);

        if (!UserPermissions::canManageProject($model)) {
            throw new ForbiddenHttpException(Yii::t('project', 'You can not update this project.'));
        }

        if (Yii::$app->user->can(UserPermissions::MANAGE_PROJECTS)) {
            $model->setScenario(Project::SCENARIO_MANAGE);
        }

        $imageUploadForm = null;

        if (UserPermissions::canManageProject($model)) {
            $imageUploadForm = new ImageUploadForm($model->id);
            if ($imageUploadForm->load(Yii::$app->request->post())) {
                $imageUploadForm->file = UploadedFile::getInstance($imageUploadForm, 'file');
                if ($imageUploadForm->upload()) {
                    return $this->refresh();
                }
            }
        }

        return $this->render('screenshots', [
            'model' => $model,
            'imageUploadForm' => $imageUploadForm
        ]);
    }

    /**
     * @param string $uuid
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPreview($uuid)
    {
        $project = $this->findModel([
            'uuid' => $uuid,
        ]);

        return $this->render('preview', [
            'model' => $project,
        ]);
    }

    /**
     * @param string $uuid
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionPublish($uuid)
    {
        $project = $this->findModel(['uuid' => $uuid]);

        if (!UserPermissions::canManageProject($project)) {
            throw new ForbiddenHttpException(Yii::t('project', 'You can not update this project.'));
        }

        $session = Yii::$app->session;
        
        if ($project->publish()) {
            $session->setFlash('success', Yii::t('project', 'Project added!'));   
        } else {
            $session->setFlash('error', Yii::t('project', 'Failed to update project.'));
            if ($project->hasErrors()) {
                $session->addFlash('error', Html::errorSummary($project, ['showAllErrors' => true]));
            }
        }

        return $this->redirect(['view', 'uuid' => $project->uuid, 'slug' => $project->slug]);
    }

    /**
     * @param string $uuid
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionDraft($uuid)
    {
        $project = $this->findModel(['uuid' => $uuid]);

        if (!UserPermissions::canManageProject($project)) {
            throw new ForbiddenHttpException(Yii::t('project', 'You can not update this project.'));
        }

        $session = Yii::$app->session;
        
        if ($project->draft()) {
            $session->setFlash('success', Yii::t('project', 'The project has been moved to draft.'));
        } else {
            $session->setFlash('error', Yii::t('project', 'Failed to update project.'));
            if ($project->hasErrors()) {
                $session->addFlash('error', Html::errorSummary($project, ['showAllErrors' => true]));
            }
        }

        return $this->redirect(['view', 'uuid' => $project->uuid, 'slug' => $project->slug]);
    }

    /**
     * Delete project by ID.
     *
     * @param string $uuid
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionDelete($uuid)
    {
        $project = $this->findModel(['uuid' => $uuid]);

        if (!UserPermissions::canManageProject($project)) {
            throw new ForbiddenHttpException(Yii::t('project', 'You can not delete this project.'));
        }

        $session = Yii::$app->session;
        
        if ($project->remove()) {
            $session->setFlash('success', Yii::t('project', 'Project deleted.'));
            
            if (in_array(Yii::$app->user->id, array_column($project->users, 'id'))) {
                return $this->redirect(['/user/view', 'id' => Yii::$app->user->id]);    
            }

            return $this->redirect('list');
        }

        $session->setFlash('error', Yii::t('project', 'Failed to delete project.'));
        if ($project->hasErrors()) {
            $session->addFlash('error', Html::errorSummary($project, ['showAllErrors' => true]));
        }
        
        return $this->redirect(['view', 'uuid' => $project->uuid, 'slug' => $project->slug]);
    }

    /**
     * @param string $uuid
     * @param string $slug
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionView($uuid, $slug)
    {
        $project = $this->findModel([
            'uuid' => $uuid,
        ]);

        if ($project->slug !== $slug) {
            return $this->redirect(['view', 'uuid' => $uuid, 'slug' => $project->slug], 301);
        }

        return $this->render('view', [
            'model' => $project,
        ]);
    }

    /**
     * @param array $condition
     * @return Project
     * @throws NotFoundHttpException
     */
    protected function findModel($condition)
    {
        /** @var Project $model */
        $model = Project::find()
            ->publishedOrEditable()
            ->andWhere($condition)
            ->one();

        if ($model !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * @return string
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteImage()
    {
        $id = Yii::$app->request->post('id');
        if ($id === null) {
            throw new BadRequestHttpException('Image id was not provided.');
        }

        /** @var Image $image */
        $image = Image::find()->with('project')->where(['id' => $id])->one();
        if (!$image) {
            throw new NotFoundHttpException('No image was found.');
        }

        if (!UserPermissions::canManageProject($image->project)) {
            throw new ForbiddenHttpException('You are not allowed to delete this image.');
        }

        if ($image->delete()) {
            return 'OK';
        }

        throw new ServerErrorHttpException('Unable to delete image.');

    }

    /**
     * @param $term
     * @return Response
     */
    public function actionAutocompleteTags($term)
    {
        $tags = Tag::find()->where(['like', 'name', $term])->limit(10)->all();
        return $this->asJson(ArrayHelper::getColumn($tags, 'name'));
    }

    /**
     * Returns an image
     *
     * @param int $imageId id of the image to return
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionImageOriginal($imageId)
    {
        /** @var Image $image */
        $image = Image::find()
            ->with('project')
            ->where(['id' => $imageId])
            ->limit(1)
            ->one();

        if (!$image) {
            throw new NotFoundHttpException('Image not found.');
        }

        if (!UserPermissions::canManageProject($image->project)) {
            throw new ForbiddenHttpException("You don't have access to this image.");
        }

        return Yii::$app->getResponse()->sendFile($image->getOriginalPath(), $image->getOriginalFilename());
    }

    /**
     * @param int $id
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionDeleteComment($id)
    {
        $comment = Comment::findOne($id);
        if ($comment === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        
        if (!UserPermissions::canManageComments()) {
            throw new ForbiddenHttpException(Yii::t('comment', 'You can not delete this comment.'));
        }

        $project = $comment->getModel();
        if ($project === null || !$project instanceof Project) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        
        if ($comment->delete()) {
            Yii::$app->session->setFlash('success', Yii::t('comment', 'Comment deleted.'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('comment', 'Failed to delete comment.'));
        }
        
        $backUrl = Yii::$app->request->referrer ?: Url::to(['view', 'uuid' => $project->uuid, 'slug' => $project->slug]);
        
        return $this->redirect($backUrl);
    }
}

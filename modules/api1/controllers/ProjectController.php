<?php

namespace app\modules\api1\controllers;

use app\components\UserPermissions;
use app\models\ImageUploadForm;
use app\models\User;
use app\modules\api1\components\Controller;
use app\modules\api1\models\Project;
use app\modules\api1\models\ProjectSearch;
use app\modules\api1\models\Vote;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\UploadedFile;

class ProjectController extends Controller
{

    /**
     * @inheritdoc
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD'],
            'view' => ['GET', 'HEAD'],
            'update' => ['PUT', 'PATCH'],
            'vote' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }

    /**
     * @return \yii\data\ActiveDataProvider
     * @throws BadRequestHttpException
     */
    public function actionIndex()
    {
        $projectSearch = new ProjectSearch();
        $projectSearch->load(\Yii::$app->request->get());
        if (!$projectSearch->validate()) {
            throw new BadRequestHttpException('Invalid parameters: ' . json_encode($projectSearch->getErrors()));
        }

        return $projectSearch->getDataProvider();
    }

    /**
     * @param string $uuid
     * @return Project|array|\yii\db\ActiveRecord
     */
    public function actionView($uuid)
    {
        return Project::find()->where(['uuid' => $uuid])->published()->one();
    }

    public function actionCreate()
    {
        $model = new \app\models\Project();
        if (UserPermissions::canManageProjects()) {
            $model->setScenario(Project::SCENARIO_MANAGE);
        }

        if ($model->load(Yii::$app->request->getBodyParams(), '')) {
            if (!$model->save()) {
                throw new ServerErrorHttpException('Unable to save project: ' . json_encode($model->getErrors()));
            }
        }
        $model->refresh();
        return $model;
        Yii::$app->getResponse()->setStatusCode(201);

    }

    /**
     * @param string $uuid
     * @return string|Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionScreenshots($uuid)
    {
        $model = $this->findProject(['uuid' => $uuid]);

        if (!UserPermissions::canManageProject($model)) {
            throw new ForbiddenHttpException(Yii::t('project', 'You can not update this project.'));
        }

        if (Yii::$app->user->can(UserPermissions::MANAGE_PROJECTS)) {
            $model->setScenario(\app\models\Project::SCENARIO_MANAGE);
        }

        $imageUploadForm = null;
        $imageUploadForm = new ImageUploadForm($model->id);
        if ($imageUploadForm->load(Yii::$app->request->post(), '')) {
            $imageUploadForm->file = UploadedFile::getInstanceByName('file');
            if (!$imageUploadForm->upload()) {
                throw new ServerErrorHttpException('Unable to save image: ' . json_encode($imageUploadForm->getErrors()));
            }
        } else {
            throw new ServerErrorHttpException('Post data error: ' . json_encode($imageUploadForm->getErrors()));
        }

        Yii::$app->getResponse()->setStatusCode(201);

    }

    /**
     * @param string $uuid
     *
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     * @throws UnauthorizedHttpException
     */
    public function actionUpdate($uuid)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            throw new UnauthorizedHttpException('User should be authorized in order to manage project.');
        }

        $project = UserPermissions::canManageProjects() ? $this->findProject(['uuid' => $uuid]) : $this->findProject(['uuid' => $uuid], $user);
        if (!UserPermissions::canManageProject($project)) {
            throw new ForbiddenHttpException(Yii::t('project', 'You can not update this project.'));
        }
        $project->scenario = Project::SCENARIO_MANAGE;
        if ($project->load(Yii::$app->request->getBodyParams(), '')) {
            if (!$project->save()) {
                throw new ServerErrorHttpException('Unable to save project: ' . json_encode($project->getErrors()));
            }
        }

        Yii::$app->getResponse()->setStatusCode(204);
    }

    /**
     * @param sgring $uuid
     *
     * @return array
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws UnauthorizedHttpException
     */
    public function actionVote($uuid)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            throw new UnauthorizedHttpException('User should be authorized in order to manage voting.');
        }

        $project = Project::find()
            ->andWhere(['uuid' => $uuid])
            ->published()
            ->limit(1)
            ->one();

        if (!$project) {
            throw new NotFoundHttpException("The requested project does not exist.");
        }

        $value = Yii::$app->request->getBodyParam('value');

        $vote = Vote::getVote($project->id, $user->id);
        if (!$vote || $vote->value != $value) {
            if (!$vote) {
                $vote = new Vote();
                $vote->project_id = $project->id;
            }
            $vote->value = $value;

            if (!$vote->save()) {
                throw new ServerErrorHttpException('Unable to save vote: ' . json_encode($vote->getErrors()));
            }
        }

        return [
            'votingResult' => $project->votingResult
        ];
    }

    /**
     * @param string $uuid
     *
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     * @throws UnauthorizedHttpException
     */
    public function actionDelete($uuid)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            throw new UnauthorizedHttpException('User should be authorized in order to manage project.');
        }

        $project = UserPermissions::canManageProjects() ? $this->findProject(['uuid' => $uuid]) : $this->findProject(['uuid' => $uuid], $user);
        if (!UserPermissions::canManageProject($project)) {
            throw new ForbiddenHttpException(Yii::t('project', 'You can not delete this project.'));
        }

        if (!$project->remove()) {
            throw new ServerErrorHttpException('Failed to delete project: ' . json_encode($project->getErrors()));
        }

        Yii::$app->getResponse()->setStatusCode(204);
    }


    /**
     * @param array $condition
     * @param User|null $user
     *
     * @return Project|array|\yii\db\ActiveRecord
     * @throws NotFoundHttpException
     */
    protected function findProject($condition, $user = null)
    {
        $projectQuery = Project::find()
            ->where($condition)
            ->available()
            ->limit(1);

        if ($user !== null) {
            $projectQuery->hasUser($user);
        }

        $project = $projectQuery->one();

        if ($project) {
            return $project;
        }

        throw new NotFoundHttpException("The requested project does not exist.");
    }
}
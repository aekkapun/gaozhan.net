<?php

use app\components\UserPermissions;
use app\widgets\Vote;
use yii\helpers\Html;
use app\models\Project;
use app\widgets\Avatar;
use yii\helpers\Url;
use yii\helpers\Markdown;
use \yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model app\models\Project */
/* @var $management bool */

// OpenGraph metatags
$this->registerMetaTag(['property' => 'og:title', 'content' => Html::encode($model->title)]);
$this->registerMetaTag(['property' => 'og:site_name', 'content' => '高站 gaozhan.net']);
$this->registerMetaTag(['property' => 'og:url', 'content' => Url::canonical()]);

$this->title = $model->title;

$canManageProject = UserPermissions::canManageProject($model);
$management = isset($management) ? $management : null;
?>
<section class="project-view">
    <header>
        <div class="title">
            <h1>
                <?= Html::encode($model->title) ?>

                <?php if ($model->is_featured): ?>
                    <span class="featured " aria-hidden="true"></span>
                <?php endif ?>

                <?php if ($model->status !== Project::STATUS_PUBLISHED && $canManageProject): ?>
                    <span class=" label <?= $model->getStatusClass() ?>"><?= $model->getStatusLabel() ?></span>
                <?php endif ?>
            </h1>

            <?php if (!empty($model->url)): ?>
                <p class="url">
                    <span class="text-muted glyphicon glyphicon-new-window" aria-hidden="true"></span>
                    <?= Html::a(Html::encode($model->url), $model->url,['target'=>'_blank']) ?>
                </p>
            <?php endif ?>

            <p><?= Vote::widget(['project' => $model]) ?></p>
        </div>
        <div class="authors">
            <ul>
                <?php foreach ($model->users as $user): ?>
                    <li>
                        <?= Html::a(Avatar::widget(['user' => $user]) . ' @' . Html::encode($user->username),
                            ['user/view', 'id' => $user->id], ['class' => 'author']) ?>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>
        <?php if ($management !== false && $canManageProject) : ?>
            <hr/>
            <div class="management">
                <p class="time text-right">
                    <span class="glyphicon glyphicon-time" aria-hidden="true"></span>
                    <?= Yii::t('project', 'Created: ') ?>
                    <?= Yii::$app->formatter->asDate($model->created_at) ?>
                    <?= Yii::t('project', 'Updated: ') ?>
                    <?= Yii::$app->formatter->asDate($model->updated_at) ?>
                </p>

                <div class="controls">
                    <p class="text-right">
                        <?= Html::a(
                            '<i class="fa fa-pencil"></i> ' . Yii::t('project', 'Update'),
                            ['update', 'uuid' => $model->uuid],
                            ['class' => 'btn btn-primary']
                        ) ?>

                    <?php if ($model->canDraft()): ?>
                            <?= Html::a(Yii::t('project', 'Save as draft'), ['draft', 'uuid' => $model->uuid], [
                                'class' => 'btn btn-warning',
                                'data-method' => 'POST',
                            ]) ?>
                    <?php endif ?>

                    <?php if ($model->canPublish()): ?>
                            <?= Html::a(Yii::t('project', 'Publish'), ['publish', 'uuid' => $model->uuid], [
                                'class' => 'btn btn-success',
                                'data-method' => 'POST',
                            ]) ?>
                    <?php endif ?>

                    <?php if ($model->canRemove()): ?>
                            <?= Html::a(
                                '<i class="fa fa-pencil"></i> ' . Yii::t('project', 'Delete'),
                                ['delete', 'uuid' => $model->uuid], [
                                'class' => 'btn btn-danger',
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('project', 'Are you sure you want to delete this project?')
                            ]) ?>
                    <?php endif ?>
                    </p>
                </div>
            </div>
        <?php endif ?>

    </header>

    <div class="project-body">
        <div class="container">
            <div class="images">
                <?php if (empty($model->images)): ?>
                    <img class="image" src="<?= $model->getPlaceholderRelativeUrl() ?>" alt="">
                <?php else: ?>
                    <?php $i = 0; ?>
                    <?php foreach ($model->getSortedImages() as $image): ?>
                        <div class="image">
                            <a href="<?= $image->getUrl() ?>">
                                <img class="img-responsive"
                                     src="<?= $i === 0 ? $image->getBigThumbnailRelativeUrl() : $image->getThumbnailRelativeUrl() ?>"
                                     alt="">
                            </a>
                        </div>
                        <?php $i++; ?>
                    <?php endforeach ?>
                <?php endif ?>
            </div>

            <div class="information">
                <?php if ($model->is_opensource): ?>
                    <p><?= Html::a(Yii::t('project', 'Source Code'), $model->source_url, ['target' => '_blank']) ?></p>
                <?php endif ?>


                <ul class="tags">
                    <?php foreach ($model->tags as $tag): ?>
                        <li><?= Html::a(Html::encode($tag->name), ['project/list', 'tags' => $tag->name]) ?></li>
                    <?php endforeach ?>
                </ul>

                <div class="">
                    <?= HtmlPurifier::process(Markdown::process($model->getDescription(), 'gfm'), Yii::$app->params['HtmlPurifier.projectDescription']) ?>
                </div>
            </div>


        </div>
    </div>
    
    <?= \app\widgets\comment\Comment::widget([
        'model' => $model
    ]) ?>
</section>

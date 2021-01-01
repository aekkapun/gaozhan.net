<?php

/* @var $this yii\web\View */
use yii\helpers\Html;

/* @var $model app\models\Project */

$this->title = Yii::t('project', 'Update project {title}', [
    'title' => $model->title,
]);
?>

<div class="project-update">
    <ol class="wizard-progress">
        <li class="is-active" data-step="1">
            <?= Yii::t('project', 'General info') ?>
        </li>
        <li data-step="2">
            <?= Html::a(Yii::t('project', 'Screenshots'), ['project/screenshots', 'uuid' => $model->uuid]) ?>
        </li>
        <li data-step="3" class="progress__last">
            <?= Html::a(Yii::t('project', 'Preview & Approve'), ['project/preview', 'uuid' => $model->uuid]) ?>
        </li>
    </ol>

    <div class="form-box">
        <div class="form-wrapper">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>
        </div>
    </div>
</div>

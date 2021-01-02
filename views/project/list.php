<?php

use app\models\Project;
use app\models\Tag;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use \yii\widgets\ListView;
use yii\bootstrap\Alert;

/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $tagsDataProvider yii\data\ActiveDataProvider */
/* @var $filterForm \app\models\ProjectFilterForm */
/* @var $this yii\web\View */
$this->title = Yii::t('project', 'Projects');
?>
<div class="projects-list">
    <div class="projects">
        <div class="filters-wrapper">
            <?php $form = ActiveForm::begin([
                'method' => 'get',
                'action' => ['project/list'],
            ]) ?>

            <div class="tags">
                <div class="title"><?= Yii::t('project', 'Tags') ?></div>

                <?= ListView::widget([
                    'dataProvider' => $tagsDataProvider,
                    'layout' => '{items}',
                    'options' => ['class' => 'list'],
                    'itemOptions' => ['class' => 'item'],
                    'itemView' => function ($model) use ($filterForm) {
                        /** @var Tag $model */
                        return Html::a(
                            '<span class="name">' . Html::encode($model->name) . '</span>' .
                            '<span class="count">' . $model->frequency . '</span>',
                            ['/project/list', 'tags' => $model->name],
                            ['class' => $filterForm->hasTag($model->name) ? 'selected' : '']
                        );
                    }
                ]) ?>
            </div>
            <?php ActiveForm::end() ?>
        </div>

        <?= ListView::widget([
            'dataProvider' => $dataProvider,
            'layout' => '{items}{pager}',
            'options' => ['class' => 'projects-flow'],
            'itemOptions' => ['class' => 'project'],
            'itemView' => '_card',
        ]) ?>
    </div>
</div>

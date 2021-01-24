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
$this->title = Yii::t('project', 'Tags');
?>
<div class="projects-tags">
    <div class="tags">
        <?= ListView::widget([
            'dataProvider' => $tagsDataProvider,
            'layout' => '{items}',
            'options' => ['class' => 'list'],
            'itemOptions' => ['tag'=>'span','class' => 'item'],
            'itemView' => function ($model)  {
                /** @var Tag $model */

                return Html::a(
                    '<span class="name">' . Html::encode($model->name) . '</span>' .
                    ' <span class="badge">' . $model->frequency . '</span>',
                    ['/project/list', 'tags' => $model->name],
                    ['class'=>'btn btn-default']
                );
            }
        ]) ?>
    </div>
</div>

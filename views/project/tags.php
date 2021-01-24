<?php

use app\models\Project;
use app\models\Tag;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use \yii\widgets\ListView;
use yii\bootstrap\Alert;

/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $tagsDataProvider yii\data\ActiveDataProvider */
/* @var $popularTagsDataProvider yii\data\ActiveDataProvider */
/* @var $filterForm \app\models\ProjectFilterForm */
/* @var $this yii\web\View */
$this->title = Yii::t('project', 'Tags');
?>
<div class="projects-tags">
    <div class="tags">
        <h3 class="text-center"><?= Yii::t('project', 'Popular tags') ?></h3>

        <?= ListView::widget([
            'dataProvider' => $popularTagsDataProvider,
            'layout' => '{items}',
            'options' => [ 'class' => 'list text-center'],
            'itemOptions' => [ 'class' => 'item'],
            'itemView' => function ($model)  {
                /** @var Tag $model */
                return Html::a(Html::tag('span', Html::encode($model->name), ['class' => 'name']),
                        ['/project/list', 'tags' => $model->name]);
            }
        ]) ?>
        <hr>
        <?= ListView::widget([
            'dataProvider' => $tagsDataProvider,
            'layout' => '{items}',
            'options' => ['tag' => 'ul', 'class' => 'list-group'],
            'itemOptions' => ['tag' => 'li', 'class' => 'list-group-item'],
            'itemView' => function ($model) use ($maxFrequency) {
                /** @var Tag $model */
                return Html::a(Html::tag('span', Html::encode($model->name), ['class' => 'name']),
                        ['/project/list', 'tags' => $model->name])
                    . Html::tag('span', Html::encode($model->frequency), ['class' => 'badge'])
                    . Html::tag('div', '', ['class' => 'percent', 'style' => 'width:' . ($model->frequency / ($maxFrequency*1.1) * 100) . '%']);
            }
        ]) ?>
    </div>
</div>

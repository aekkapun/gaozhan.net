<?php

use yii\helpers\Html;
use \yii\widgets\ListView;

/* @var $featuredProvider yii\data\ActiveDataProvider */
/* @var $newProvider yii\data\ActiveDataProvider */
/* @var $this yii\web\View */
/* @var $projectsCount int */
/* @var $seeMoreCount int */

$this->title = Yii::t('project', 'Projects built with Yii');
?>
<div class="intro">
    <p>
        这是一个优秀网站分享平台，让更多的人从这里获取灵感
    </p>

    <?= Html::a(
        Yii::t('project', 'Made one? Share it!'),
        ['project/create'],
        ['class' => 'add-project']
    ) ?>
</div>

<div class="project-index">
    <section class="group">
        <header><?= Yii::t('project', 'Featured projects') ?></header>

        <?= ListView::widget([
            'dataProvider' => $featuredProvider,
            'layout' => "{items}\n{pager}",
            'options' => ['class' => 'projects-flow'],
            'itemOptions' => ['class' => 'project'],
            'itemView' => '_card',
            'pager' => [
                'class' => \kop\y2sp\ScrollPager::className(),
                'container' => '.projects-flow',
                'paginationSelector' => '.projects-flow .pagination',
                'item' => '.project',
                'triggerOffset' => 100,
                'spinnerTemplate' => '<div class="ias-spinner" style="width:100%; text-align: center;"><img src="{src}"/></div>',
                'triggerTemplate' => '<div class="ias-trigger" style="width:100%; text-align: center; cursor: pointer;"><a>{text}</a></div>',
                'eventOnRendered'=>'function(){lazyLoadInstance.update();}'
            ]
        ]) ?>
    </section>


</div>

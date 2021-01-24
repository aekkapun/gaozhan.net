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
$this->title = Yii::$app->request->get('tags') ? Yii::$app->request->get('tags') : Yii::t('app', 'Explore projects');
?>
<div class="projects-list">
    <div class="projects">
        <div class="filters-wrapper">
            <?php $form = ActiveForm::begin([
                'method' => 'get',
                'action' => ['project/list'],
            ]) ?>

            <div class="tags">
                <div class="title"><?= Yii::t('project', 'Filters') ?></div>

                <?php
                $tags = [
                    [
                        'type' => '颜色',
                        'class' => '',
                        'list' => [
                            ['name' => 'white', 'label' => '白 white', 'image' => '/img/colors/icon_white.gif'],
                            ['name' => 'black', 'label' => '黑 black', 'image' => '/img/colors/icon_black.gif'],
                            ['name' => 'gray', 'label' => '灰 gray', 'image' => '/img/colors/icon_gray.gif'],
                            ['name' => 'brown', 'label' => '褐 brown', 'image' => '/img/colors/icon_brown.gif'],
                            ['name' => 'pink', 'label' => '粉 pink', 'image' => '/img/colors/icon_pink.gif'],
                            ['name' => 'red', 'label' => '红 red', 'image' => '/img/colors/icon_red.gif'],
                            ['name' => 'orange', 'label' => '橙 orange', 'image' => '/img/colors/icon_orange.gif'],
                            ['name' => 'yellow', 'label' => '黄 yellow', 'image' => '/img/colors/icon_yellow.gif'],
                            ['name' => 'green', 'label' => '绿 green', 'image' => '/img/colors/icon_green.gif'],
                            ['name' => 'blue', 'label' => '蓝 blue', 'image' => '/img/colors/icon_blue.gif'],
                            ['name' => 'purple', 'label' => '紫 purple', 'image' => '/img/colors/icon_purple.gif'],
                            ['name' => 'colorful', 'label' => '彩 colorful', 'image' => '/img/colors/icon_colorful.gif'],
                        ]
                    ],
                    [
                        'type' => '题材',
                        'class' => '',
                        'list' => [
                            ['name' => 'corporate', 'label' => '公司 corporate', 'image' => ''],
                            ['name' => 'product', 'label' => '产品 product', 'image' => ''],
                            ['name' => 'promotion', 'label' => '活动 promotion', 'image' => ''],
                            ['name' => 'branding', 'label' => '品牌 branding', 'image' => ''],
                            ['name' => 'e-commerce', 'label' => '电商 e-commerce', 'image' => ''],
                            ['name' => 'portal', 'label' => '门户 portal', 'image' => ''],
                            ['name' => 'service', 'label' => '服务 service', 'image' => ''],
                            ['name' => 'blog', 'label' => '博客 blog', 'image' => ''],
                            ['name' => 'portfolio', 'label' => '案例 portfolio', 'image' => ''],
                            ['name' => 'fashion', 'label' => '时尚 fashion', 'image' => ''],
                            ['name' => 'photograph', 'label' => '摄影 photograph', 'image' => ''],
                            ['name' => 'business', 'label' => '商业 business', 'image' => ''],
                            ['name' => 'event', 'label' => '事件 event', 'image' => ''],
                            ['name' => 'personal', 'label' => '个人 personal', 'image' => ''],
                            ['name' => 'handwriting', 'label' => '笔迹 handwriting', 'image' => ''],
                        ]
                    ],
                    [
                        'type' => '语言',
                        'class' => '',
                        'list' => [
                            ['name' => 'japanese', 'label' => '日语', 'image' => ''],
                            ['name' => 'english', 'label' => '英语', 'image' => ''],
                            ['name' => 'other-language', 'label' => '其他', 'image' => ''],
                        ]
                    ],
                    [
                        'type' => '布局',
                        'class' => 'pattern col-md-6',
                        'list' => [
                            ['name' => 'pattern-a', 'label' => 'pattern-a', 'image' => '/img/patterns/icon_pattern-a.gif'],
                            ['name' => 'pattern-b', 'label' => 'pattern-b', 'image' => '/img/patterns/icon_pattern-b.gif'],
                            ['name' => 'pattern-c', 'label' => 'pattern-c', 'image' => '/img/patterns/icon_pattern-c.gif'],
                            ['name' => 'pattern-d', 'label' => 'pattern-d', 'image' => '/img/patterns/icon_pattern-d.gif'],
                            ['name' => 'pattern-e', 'label' => 'pattern-e', 'image' => '/img/patterns/icon_pattern-e.gif'],
                            ['name' => 'pattern-f', 'label' => 'pattern-f', 'image' => '/img/patterns/icon_pattern-f.gif'],
                            ['name' => 'pattern-g', 'label' => 'pattern-g', 'image' => '/img/patterns/icon_pattern-g.gif'],
                            ['name' => 'pattern-h', 'label' => 'pattern-h', 'image' => '/img/patterns/icon_pattern-h.gif'],
                            ['name' => 'pattern-i', 'label' => 'pattern-i', 'image' => '/img/patterns/icon_pattern-i.gif'],
                            ['name' => 'pattern-j', 'label' => 'pattern-j', 'image' => '/img/patterns/icon_pattern-j.gif'],
                            ['name' => 'pattern-k', 'label' => 'pattern-k', 'image' => '/img/patterns/icon_pattern-k.gif'],
                        ]
                    ],
                ];
                foreach ($tags as $type) {
                    echo Html::tag('h5', $type['type']);
                    foreach ($type['list'] as $tag) {
                        echo Html::tag('div',
                            Html::a(
                                $tag['image'] ? Html::img($tag['image']) . ' ' . $tag['label'] : $tag['label'],
                                ['/project/list', 'tags' => $tag['name']],
                                ['class' => $filterForm->hasTag($tag['name']) ? 'selected' : '']
                            ), ['class' => $type['class']]);
                    }
                }
                ?>

            </div>
            <?php ActiveForm::end() ?>
        </div>

        <?= ListView::widget([
            'dataProvider' => $dataProvider,
            'layout' => '{items}{pager}',
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
                'noneLeftTemplate' => '<div class="ias-noneleft" style="width:100%; text-align: center;">{text}</div>',
                'triggerTemplate' => '<div class="ias-trigger" style="width:100%; text-align: center; cursor: pointer;"><a>{text}</a></div>',
                'eventOnRendered' => 'function(){lazyLoadInstance.update();}'
            ]
        ]) ?>
    </div>
</div>

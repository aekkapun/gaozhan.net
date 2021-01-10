<?php

use app\helpers\GoogleAnalytics;
use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - 高站 - 高质量网站推荐</title>
    <link rel="alternate" type="application/rss+xml" title="高站"
          href="<?= \yii\helpers\Url::to(['project/rss'], true) ?>"/>
    <?php $this->head() ?>
    <?= Yii::$app->params['tongji'] ?>
    <meta name="baidu-site-verification" content="code-IPjauywEvk"/>
</head>
<body>

<?php $this->beginBody() ?>
<div class="wrap">
    <div class="content">
        <?php
        NavBar::begin([
            'brandLabel' => '<span class="gaozhan-logo"></span>高站',
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar-default navbar-fixed-top',
            ],
        ]);

        $menuItems = [
            [
                'label' => Html::tag('span', Yii::t('app', 'Featured projects')),
                'encode' => false,
                'url' => ['/'],
                'linkOptions' => ['alt' => Yii::t('app', 'Featured projects'), 'title' => Yii::t('app', 'Featured projects')],
            ],
            [
                'label' => Html::tag('span', Yii::t('app', 'Explore projects')),
                'encode' => false,
                'url' => ['/projects'],
                'linkOptions' => ['alt' => Yii::t('app', 'Explore projects'), 'title' => Yii::t('app', 'Explore projects')],
            ],

            [
                'label' => Yii::t('project', 'Top {n}', ['n' => Yii::$app->params['project.maxTopProjects']]),
                'url' => ['/project/top-projects'],
            ]
        ];
        /*$menuItems[] = [
            'label' => Html::tag('span', '<span> ' . Yii::t('app', 'RSS feed') . '</span>', ['class' => 'fa fa-rss-square']),
            'encode' => false,
            'url' => ['/project/rss'],
            'linkOptions' => [
                'alt' => Yii::t('app', 'RSS feed'),
                'title' => Yii::t('app', 'RSS feed'),
            ]
        ]*/
        ?>
        <?= Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-left'],
            'items' => $menuItems,
        ]); ?>
        <?php
        $menuItems = [];
        if (Yii::$app->user->isGuest) {
            $menuItems[] = [
                'label' => Html::tag('span', Yii::t('app', 'Login')),
                'encode' => false,
                'url' => ['/site/login'],
                'linkOptions' => ['alt' => Yii::t('app', 'Login'), 'title' => Yii::t('app', 'Login')],
            ];
        } else {
            $menuItems[] = [
                'label' => Yii::t('user', 'Manage users'),
                'url' => ['/user/index'],
                'visible' => \Yii::$app->user->can('manage_users'),
                'items' => [

                ]
            ];
            $menuItems[] = [
                'label' => Html::img(Yii::$app->user->identity->getAvatarImage(), ['alt' => Yii::$app->user->identity->username, 'class' => 'img-circle', 'width' => 20]),
                'encode' => false,
                'items' => [
                    [
                        'label' => Html::tag('span', Yii::$app->user->identity->username),
                        'url' => ['/user/view', 'id' => \Yii::$app->user->id],
                        'encode' => false,
                    ],
                    [
                        'label' => Html::tag('span', Yii::t('app', 'Bookmarks')),
                        'encode' => false,
                        'url' => ['/project/bookmarks'],
                        'linkOptions' => ['alt' => Yii::t('app', 'Bookmarks'), 'title' => Yii::t('app', 'Bookmarks')],
                    ],
                    [
                        'label' => Html::tag('span', Yii::t('app', 'Logout')),
                        'url' => ['/site/logout'],
                        'encode' => false,
                        'linkOptions' => [
                            'data-method' => 'post',
                            'alt' => Yii::t('app', 'Logout'),
                            'title' => Yii::t('app', 'Logout'),
                        ],
                    ]
                ]
            ];
        }
        $menuItems[] = [
            'label' => Yii::t('project', 'Add project'),
            'url' => ['project/create'],
            'linkOptions' => ['class' => 'btn-add-project']
        ];
        ?>
        <?= Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-right'],
            'items' => $menuItems,
        ]); ?>

        <?php NavBar::end(); ?>

        <div class="content-wrapper">
            <?= Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>
            <?php if (\Yii::$app->getSession()->getAllFlashes()): ?>
                <div class="container container-alert">
                    <div class="row">
                        <div class="col-xs-12">
                            <?= Alert::widget() ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?= $content ?>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="left">
                &copy; 高站 <?= date('Y') ?> |

                <?= Html::a(Yii::t('app', 'About'), ['/site/about']) ?>
            </div>

            <div class="right">
                <?= Yii::$app->params['beian'] ?>
                 | <?= Yii::powered() ?> | <a href="https://www.bestyii.com">Yii 中文社区</a>
            </div>
        </div>
    </footer>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

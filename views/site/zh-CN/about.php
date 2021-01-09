<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
$this->title = '关于高站';
?>
<div class="site-about">
    <div class="panes-wrapper">
        <div class="pane">
            <h1><?= Html::encode($this->title) ?></h1>
            <h3>
                为什么叫"高站"?
            </h3>
            <p>
                网站的宗旨是
            </p>
            <p class="lead"><i>-- 打造高质量网站分享平台让更多设计湿获得灵感</i></p>
            <p>
                所以"高站"一词的来源是，高质量网站的简写
            </p>

            <h2>维护团队</h2>
            <p><a href="https://www.bestyii.com" target="_blank">Yii 中文社区技术组</a></p>
            <ul>
                <li>设计：edge</li>
                <li>开发：ez</li>
            </ul>
            <h2>联系我们</h2>
            <p>
            欢迎进入<a href="https://www.bestyii.com/?node=gaozhan" target="_blank">站务管理</a>进行交流

            </p>
        </div>

        <div class="pane">
            <h2>感谢</h2>

            <p>
                本站采用基于<a href="http://www.yiiframework.com/">yiiframework</a> 框架的开源项目 <a href="https://github.com/samdark/yiipowered">yiipowered</a> 建立.
            </p>
        </div>
    </div>
</div>

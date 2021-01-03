<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = $name;
?>
<div class="site-error">
    <div class="center-box">
        <h1><?= Html::encode($this->title) ?></h1>

        <div class="alert alert-danger">
            <?= nl2br(Html::encode($message)) ?>
        </div>

        <p>
            发生错误 :(
        </p>
        <p>
            可以通过电邮来告诉我们你遇到的情况 <a href="mailto:ezsky@gaozhan.net">@ezsky</a>.
        </p>
    </div>
</div>

<?php

use yii\helpers\Html;

$this->title = 'О нас';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        Это страница "О нас". Вы можете изменить следующее содержимое, чтобы настроить его под свои нужды:
    </p>

    <code><?= __FILE__ ?></code>
</div>

<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Account\Account */

$this->title = 'Создать счет';
$this->params['breadcrumbs'][] = ['label' => 'Счета', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="account-create">
    <div class="page-header">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>

    <div class="account-form">
        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>
</div>
<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Account\Account */

$this->title = 'Обновить счет: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Счета', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Обновить';
?>
<div class="account-update">
    <div class="page-header">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>

    <div class="account-form">
        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>
</div>

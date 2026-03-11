<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Earnings\EarningsSource */

$this->title = 'Обновить источник заработка: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Источники заработка', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Обновить';
?>
<div class="earnings-source-update">
    <div class="page-header">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>

    <div class="earnings-source-form">
        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>
</div>

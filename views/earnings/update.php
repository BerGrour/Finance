<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Earnings\Earnings */

$this->title = 'Обновить заработок: #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Заработок', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Заработок #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Обновить';
?>
<div class="earnings-update">
    <div class="page-header">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>

    <div class="earnings-form">
        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>
</div>

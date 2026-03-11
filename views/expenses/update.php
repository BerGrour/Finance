<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Expenses\Expenses */

$this->title = 'Обновить трату: #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Траты', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Трата #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Обновить';
?>
<div class="expenses-update">
    <div class="page-header">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>

    <div class="expenses-form">
        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>
</div>

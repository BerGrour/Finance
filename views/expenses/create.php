<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Expenses\Expenses */

$this->title = 'Создать трату';
$this->params['breadcrumbs'][] = ['label' => 'Траты', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="expenses-create">
    <div class="page-header">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>

    <div class="expenses-form">
        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>
</div>

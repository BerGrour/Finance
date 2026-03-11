<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Earnings\EarningsSource */

$this->title = 'Создать источник заработка';
$this->params['breadcrumbs'][] = ['label' => 'Источники заработка', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="earnings-source-create">
    <div class="page-header">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>

    <div class="earnings-source-form">
        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>
</div>

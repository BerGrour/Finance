<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Account\Account */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Счета', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="account-view">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <div>
            <?= Html::a('Обновить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы уверены, что хотите удалить этот счет?',
                    'method' => 'post',
                ],
            ]) ?>
            <?= Html::a('Пересчитать баланс', ['recalculate', 'id' => $model->id], [
                'class' => 'btn btn-info',
                'data' => [
                    'confirm' => 'Пересчитать баланс счета?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'attribute' => 'user_id',
                'value' => $model->user ? $model->user->username : $model->user_id,
                'visible' => false,
            ],
            'name',
            [
                'attribute' => 'type',
                'value' => $model->getTypeName(),
            ],
            [
                'attribute' => 'initial_balance',
                'label' => 'Начальный баланс',
                'value' => $model->currency ? $model->currency->formatAmount($model->initial_balance ?? 0) : Yii::$app->formatter->asCurrency($model->initial_balance ?? 0, 'RUB'),
            ],
            [
                'attribute' => 'balance',
                'value' => $model->currency ? $model->currency->formatAmount($model->balance) : Yii::$app->formatter->asCurrency($model->balance, 'RUB'),
            ],
            [
                'attribute' => 'currency_id',
                'value' => $model->currency ? $model->currency->code . ' - ' . $model->currency->name : $model->currency_id,
            ],
            [
                'attribute' => 'is_deleted',
                'format' => 'boolean',
                'visible' => !$model->is_deleted
            ],
            [
                'attribute' => 'is_default',
                'format' => 'boolean',
            ],
            'comment:ntext',
            [
                'attribute' => 'created_at',
                'format' => ['datetime', 'php:d.m.Y H:i'],
            ],
        ],
    ]) ?>
</div>

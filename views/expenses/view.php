<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Expenses\Expenses */

$this->title = 'Трата #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Траты', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="expenses-view">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <div>
            <?= Html::a('Обновить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы уверены, что хотите удалить эту трату?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'attribute' => 'date_time',
                'label' => 'Дата и время',
                'value' => Yii::$app->formatter->asDatetime($model->date_time, 'php:d.m.Y H:i'),
            ],
            [
                'attribute' => 'amount',
                'label' => 'Сумма',
                'value' => $model->account && $model->account->currency 
                    ? $model->account->currency->formatAmount($model->amount)
                    : Yii::$app->formatter->asCurrency($model->amount, 'RUB'),
            ],
            [
                'attribute' => 'category',
                'value' => $model->getCategoryName(),
            ],
            [
                'attribute' => 'status',
                'value' => $model->getStatusName(),
            ],
            [
                'attribute' => 'account_id',
                'label' => 'Счет',
                'value' => $model->account ? $model->account->name : '',
            ],
            'comment:ntext',
            [
                'attribute' => 'is_counted_in_stats',
                'value' => $model->is_counted_in_stats ? 'Да' : 'Нет',
            ],
            [
                'attribute' => 'created_at',
                'format' => ['datetime', 'php:d.m.Y H:i'],
            ],
            [
                'attribute' => 'updated_at',
                'format' => ['datetime', 'php:d.m.Y H:i'],
            ],
        ],
    ]) ?>
</div>

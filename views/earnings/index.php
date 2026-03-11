<?php

use app\models\Earnings\Earnings;
use app\models\Earnings\EarningsSource;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel app\models\Earnings\EarningsSearch */

$this->title = 'Заработок';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="earnings-index">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1><?= Html::encode($this->title) ?></h1>
            <p class="text-muted">Управление вашими доходами</p>
        </div>
        <div>
            <?= Html::a('<span class="glyphicon glyphicon-bookmark"></span> Источники заработка', ['/earnings-source/index'], ['class' => 'btn btn-info']) ?>
            <?= Html::a('<span class="glyphicon glyphicon-plus"></span> Создать заработок', ['create'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <?php Pjax::begin(); ?>

    <?php
    $gridColumns = [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'date_time',
            'label' => 'Дата и время',
            'value' => function ($model) {
                return Yii::$app->formatter->asDatetime($model->date_time, 'php:d.m.Y H:i');
            },
            'filter' => Html::tag('div',
                Html::activeTextInput($searchModel, 'date_from', [
                    'class' => 'form-control',
                    'placeholder' => 'С',
                    'style' => 'min-width:110px',
                ]) .
                Html::tag('span', '—', ['class' => 'mx-1 align-self-center']) .
                Html::activeTextInput($searchModel, 'date_to', [
                    'class' => 'form-control',
                    'placeholder' => 'По',
                    'style' => 'min-width:110px',
                ]),
                ['style' => 'display:flex;align-items:center;gap:0;white-space:nowrap']
            ),
        ],
        [
            'attribute' => 'amount',
            'label' => 'Сумма',
            'value' => function ($model) {
                if ($model->account && $model->account->currency) {
                    return $model->account->currency->formatAmount($model->amount);
                }
                return Yii::$app->formatter->asCurrency($model->amount, 'RUB');
            },
            'contentOptions' => ['class' => 'text-end'],
        ],
        [
            'attribute' => 'category',
            'value' => function ($model) {
                return $model->getCategoryName();
            },
            'filter' => Earnings::getCategoryList(),
        ],
        [
            'attribute' => 'source_id',
            'label' => 'Источник',
            'value' => function ($model) {
                return $model->source ? $model->source->name : '';
            },
            'filter' => EarningsSource::toList(),
        ],
        [
            'attribute' => 'status',
            'value' => function ($model) {
                return $model->getStatusName();
            },
            'filter' => Earnings::getStatusList(),
        ],
        [
            'attribute' => 'account_id',
            'label' => 'Счет',
            'value' => function ($model) {
                return $model->account ? $model->account->name : '';
            },
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view} {update} {delete}',
        ],
    ];
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $gridColumns,
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
        'options' => ['class' => 'table-responsive'],
    ]); ?>

    <?php Pjax::end(); ?>
</div>

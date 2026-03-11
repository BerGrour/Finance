<?php

use app\models\Expenses\Expenses;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel app\models\Expenses\ExpensesSearch */

$this->title = 'Траты';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="expenses-index">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1><?= Html::encode($this->title) ?></h1>
            <p class="text-muted">Управление вашими расходами</p>
        </div>
        <div>
            <?= Html::a('<span class="glyphicon glyphicon-plus"></span> Создать трату', ['create'], ['class' => 'btn btn-success']) ?>
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
            'filter' => Expenses::getCategoryList(),
        ],
        [
            'attribute' => 'status',
            'value' => function ($model) {
                return $model->getStatusName();
            },
            'filter' => Expenses::getStatusList(),
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

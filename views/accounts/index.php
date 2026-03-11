<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel app\models\Account\AccountSearch */

$this->title = 'Счета';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="account-index">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1><?= Html::encode($this->title) ?></h1>
            <p class="text-muted">Управление вашими счетами и кошельками</p>
        </div>
        <div>
            <?= Html::a('<span class="glyphicon glyphicon-plus"></span> Создать счет', ['create'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <?php Pjax::begin(); ?>

    <?php
    $gridColumns = [
        ['class' => 'yii\grid\SerialColumn'],
        'name',
        [
            'attribute' => 'type',
            'value' => function ($model) {
                return $model->getTypeName();
            },
        ],
        [
            'attribute' => 'balance',
            'label' => 'Баланс',
            'value' => function ($model) {
                if ($model->currency) {
                    return $model->currency->formatAmount($model->balance);
                }
                return Yii::$app->formatter->asCurrency($model->balance, 'RUB');
            },
            'contentOptions' => ['class' => 'text-end'],
        ],
        [
            'attribute' => 'is_deleted',
            'format' => 'boolean',
            'filter' => [1 => 'Да', 0 => 'Нет'],
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view} {update} {delete} {recalculate}',
            'buttons' => [
                'recalculate' => function ($url, $model) {
                    return Html::a(
                        '<span class="glyphicon glyphicon-calculator" title="Пересчитать баланс"></span>',
                        $url,
                        ['data-pjax' => '0']
                    );
                },
            ],
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

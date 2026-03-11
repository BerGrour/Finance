<?php

use app\models\Earnings\EarningsSource;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel app\models\Earnings\EarningsSourceSearch */

$this->title = 'Источники заработка';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="earnings-source-index">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1><?= Html::encode($this->title) ?></h1>
            <p class="text-muted">Управление источниками ваших доходов</p>
        </div>
        <div>
            <?= Html::a('<span class="glyphicon glyphicon-list"></span> Заработок', ['/earnings/index'], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<span class="glyphicon glyphicon-plus"></span> Создать источник', ['create'], ['class' => 'btn btn-success']) ?>
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
            'filter' => EarningsSource::getTypeList(),
        ],
        [
            'attribute' => 'created_at',
            'format' => ['datetime', 'php:d.m.Y H:i'],
            'filter' => false,
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

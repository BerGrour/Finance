<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Earnings\EarningsSource */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Источники заработка', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="earnings-source-view">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <div>
            <?= Html::a('Обновить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы уверены, что хотите удалить этот источник заработка?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            [
                'attribute' => 'type',
                'value' => $model->getTypeName(),
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

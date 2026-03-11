<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Earnings\Earnings */

$this->title = 'Создать заработок';
$this->params['breadcrumbs'][] = ['label' => 'Заработок', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="earnings-create">
    <div class="page-header">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>

    <div class="earnings-form">
        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>
</div>

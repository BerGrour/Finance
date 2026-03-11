<?php

use app\models\Earnings\EarningsSource;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Earnings\EarningsSource */
/* @var $form yii\bootstrap5\ActiveForm */
?>

<?php $form = ActiveForm::begin([
    'id' => 'earnings-source-form',
    'layout' => 'horizontal',
    'fieldConfig' => [
        'template' => "{label}\n<div class=\"col-lg-9\">{input}</div>\n<div class=\"col-lg-9 offset-lg-3\">{error}</div>",
        'labelOptions' => ['class' => 'col-lg-3 col-form-label'],
    ],
]); ?>

<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

<?= $form->field($model, 'type')->dropDownList(
    EarningsSource::getTypeList(),
    [
        'prompt' => 'Выберите тип источника...',
        'class' => 'form-control',
    ]
) ?>

<div class="form-group">
    <div class="col-lg-9 offset-lg-3">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Отмена', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>

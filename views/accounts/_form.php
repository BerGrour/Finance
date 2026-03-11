<?php

use app\models\Account\Account;
use app\models\Currency\Currency;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\Account\Account */
/* @var $form yii\bootstrap5\ActiveForm */
?>

<?php $form = ActiveForm::begin([
    'id' => 'account-form',
    'layout' => 'horizontal',
    'fieldConfig' => [
        'template' => "{label}\n<div class=\"col-lg-9\">{input}</div>\n<div class=\"col-lg-9 offset-lg-3\">{error}</div>",
        'labelOptions' => ['class' => 'col-lg-3 col-form-label'],
    ],
]); ?>

<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

<?= $form->field($model, 'type')->dropDownList(
    Account::getTypeList(),
    [
        'prompt' => 'Выберите тип счета...',
        'class' => 'form-control',
    ]
) ?>

<div class="form-group">
    <?= $form->field($model, 'currency_id')->widget(Select2::class, [
        'data' => Currency::getListWithCode(),
        'language' => 'ru',
        'options' => [
            'placeholder' => 'Выберите валюту...',
            'class' => 'form-control',
        ],
        'pluginOptions' => [
            'allowClear' => true,
        ],
    ]) ?>
</div>

<?= $form->field($model, 'initial_balance')->textInput([
    'type' => 'number',
    'step' => '0.01',
    'placeholder' => '0.00'
])->hint('Начальный баланс счета при создании. Баланс будет автоматически обновляться при добавлении заработков и трат.') ?>

<div class="form-group">
    <label class="col-lg-3 col-form-label"></label>
    <div class="col-lg-9">
        <?= $form->field($model, 'is_default')->checkbox([
            'labelOptions' => ['class' => null],
        ])->label('Счет по умолчанию') ?>
    </div>
</div>

<?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>

<div class="form-group">
    <div class="col-lg-9 offset-lg-3">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Отмена', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>

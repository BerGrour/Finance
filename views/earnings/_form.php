<?php

use app\models\Account\Account;
use app\models\Earnings\Earnings;
use app\models\Earnings\EarningsSource;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap5\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Earnings\Earnings */
/* @var $form yii\bootstrap5\ActiveForm */
?>

<?php $form = ActiveForm::begin([
    'id' => 'earnings-form',
    'layout' => 'horizontal',
    'fieldConfig' => [
        'template' => "{label}\n<div class=\"col-lg-9\">{input}</div>\n<div class=\"col-lg-9 offset-lg-3\">{error}</div>",
        'labelOptions' => ['class' => 'col-lg-3 col-form-label'],
    ],
]); ?>

<?= $form->field($model, 'amount')->textInput(['type' => 'number', 'step' => '0.01', 'min' => '0.01']) ?>

<div class="row mb-3 field-earnings-datetime required<?= $model->hasErrors('date_time') ? ' has-error' : '' ?>">
    <label class="col-lg-3 col-form-label">Дата и время <span class="text-danger">*</span></label>
    <div class="col-lg-9">
        <div class="row">
            <div class="col-md-6" style="padding-right: 5px;">
                <?= DatePicker::widget([
                    'name' => 'date',
                    'value' => $model->date_time ? $model->getDate() : date('Y-m-d'),
                    'options' => [
                        'placeholder' => 'Выберите дату...', 
                        'id' => 'earnings-date',
                        'required' => true,
                        'class' => 'form-control',
                    ],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true,
                        'language' => 'ru',
                    ],
                ]) ?>
            </div>
            <div class="col-md-6" style="padding-left: 5px;">
                <?= Html::dropDownList('time', $model->date_time ? $model->getTime() : '', 
                    Earnings::getTimeList(),
                    [
                        'class' => 'form-control',
                        'id' => 'earnings-time',
                        'prompt' => 'Выберите время (по умолчанию 00:00)...'
                    ]
                ) ?>
            </div>
        </div>
        <?php if ($model->hasErrors('date_time')): ?>
            <div class="help-block" style="margin-top: 5px;"><?= $model->getFirstError('date_time') ?></div>
        <?php endif; ?>
    </div>
</div>

<?= $form->field($model, 'category')->dropDownList(
    Earnings::getCategoryList(),
    [
        'prompt' => 'Выберите категорию...',
        'class' => 'form-control',
    ]
) ?>

<?= $form->field($model, 'source_id')->widget(Select2::class, [
    'data' => ArrayHelper::map(
        EarningsSource::findByUser()
            ->andWhere(['is_static' => 0])
            ->all(),
        'id',
        'name'
    ),
    'language' => 'ru',
    'options' => [
        'placeholder' => 'Выберите источник...',
        'class' => 'form-control',
    ],
    'pluginOptions' => [
        'allowClear' => true,
    ],
]) ?>

<?= $form->field($model, 'account_id')->widget(Select2::class, [
    'data' => ArrayHelper::map(
        Account::findByUser()
            ->all(),
        'id',
        'name'
    ),
    'language' => 'ru',
    'options' => [
        'placeholder' => 'Выберите счет...',
        'class' => 'form-control',
    ],
    'pluginOptions' => [
        'allowClear' => true,
    ],
]) ?>

<?= $form->field($model, 'status')->dropDownList(
    Earnings::getStatusList(),
    [
        'prompt' => 'Выберите статус...',
        'class' => 'form-control',
    ]
) ?>

<?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>

<?= $form->field($model, 'is_counted_in_stats', [
    'template' => "<div class=\"col-lg-9 offset-lg-3\">{input}\n{error}</div>",
])->checkbox([
    'label' => Yii::t('app', 'Учитывать в статистике'),
    'id' => 'earnings-is_counted_in_stats',
    'uncheck' => '0',
]) ?>

<div class="form-group">
    <div class="col-lg-9 offset-lg-3">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Отмена', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>

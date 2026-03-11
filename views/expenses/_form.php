<?php

use app\models\Account\Account;
use app\models\Expenses\Expenses;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\bootstrap5\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Expenses\Expenses */
/* @var $form yii\bootstrap5\ActiveForm */
?>

<?php $this->registerJsFile(Url::to('@web/js/pages/expenses/form.js'), [
    'depends' => [\yii\web\YiiAsset::class],
]);
?>
<?php $form = ActiveForm::begin([
    'id' => 'expenses-form',
    'layout' => 'horizontal',
    'options' => [
        'data' => [
            'category-transfer' => (int) Expenses::CATEGORY_TRANSFER,
        ],
    ],
    'fieldConfig' => [
        'template' => "{label}\n<div class=\"col-lg-9\">{input}</div>\n<div class=\"col-lg-9 offset-lg-3\">{error}</div>",
        'labelOptions' => ['class' => 'col-lg-3 col-form-label'],
    ],
]); ?>

<?= $form->field($model, 'amount')->textInput(['type' => 'number', 'step' => '0.01', 'min' => '0.01']) ?>

<div class="row mb-3 field-expenses-datetime required<?= $model->hasErrors('date_time') ? ' has-error' : '' ?>">
    <label class="col-lg-3 col-form-label">Дата и время <span class="text-danger">*</span></label>
    <div class="col-lg-9">
        <div class="row">
            <div class="col-md-6" style="padding-right: 5px;">
                <?= DatePicker::widget([
                    'name' => 'date',
                    'value' => $model->date_time ? $model->getDate() : date('Y-m-d'),
                    'options' => [
                        'placeholder' => 'Выберите дату...', 
                        'id' => 'expenses-date',
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
                    Expenses::getTimeList(),
                    [
                        'class' => 'form-control',
                        'id' => 'expenses-time',
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
    Expenses::getCategoryList(),
    [
        'prompt' => 'Выберите категорию...',
        'class' => 'form-control',
        'id' => 'expenses-category',
    ]
) ?>

<div id="transfer-account-field" style="display: <?= $model->category === Expenses::CATEGORY_TRANSFER ? 'block' : 'none' ?>;">
    <div class="row mb-3">
        <label class="col-lg-3 col-form-label">Счет получателя <span class="text-danger">*</span></label>
        <div class="col-lg-9">
            <?= Select2::widget([
                'name' => 'transfer_account_id',
                'data' => ArrayHelper::map(
                    Account::findByUser()
                        ->all(),
                    'id',
                    'name'
                ),
                'value' => $model->transfer_account_id ?? null,
                'language' => 'ru',
                'options' => [
                    'placeholder' => 'Выберите счет получателя...',
                    'class' => 'form-control',
                    'id' => 'transfer-account-select',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]) ?>
        </div>
    </div>
</div>

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
    Expenses::getStatusList(),
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
    'id' => 'expenses-is_counted_in_stats',
    // 'uncheck' => '0',
]) ?>

<div class="form-group">
    <div class="col-lg-9 offset-lg-3">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Отмена', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>

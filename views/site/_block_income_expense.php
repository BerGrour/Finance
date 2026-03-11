<?php

use app\models\Currency\Currency;
use yii\helpers\Url;

/**
 * Частичное представление блока "Доходы / Расходы"
 * 
 * @var float $periodEarnings Доходы за последние 30 дней
 * @var float $periodExpenses Расходы за последние 30 дней
 * @var float $earningsChangePercent Процент изменения доходов
 * @var float $expensesChangePercent Процент изменения расходов
 * @var \app\models\Currency\Currency|null $defaultCurrency Валюта по умолчанию
 */
?>
<div class="income-expense-block mb-4">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card shadow-sm income-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="income-icon me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-arrow-up-circle text-success" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v5.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V4.5z"/>
                            </svg>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Доходы</h6>
                            <h3 class="mb-0">
                                <?php
                                $earningsCurrency = $defaultCurrency ?? Currency::findByCode('RUB');
                                echo $earningsCurrency ? $earningsCurrency->formatAmount($periodEarnings ?? 0) : Yii::$app->formatter->asCurrency($periodEarnings ?? 0, 'RUB');
                                ?>
                            </h3>
                            <?php if (isset($earningsChangePercent) && $earningsChangePercent != 0): ?>
                                <small class="<?= $earningsChangePercent >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-arrow-<?= $earningsChangePercent >= 0 ? 'up' : 'down' ?>" viewBox="0 0 16 16">
                                        <?php if ($earningsChangePercent >= 0): ?>
                                            <path fill-rule="evenodd" d="M8 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L7.5 2.707V14.5A.5.5 0 0 0 8 15z"/>
                                        <?php else: ?>
                                            <path fill-rule="evenodd" d="M8 1a.5.5 0 0 1 .5.5v11.793l3.146-3.147a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 .708-.708L7.5 13.293V1.5A.5.5 0 0 1 8 1z"/>
                                        <?php endif; ?>
                                    </svg>
                                    <?= $earningsChangePercent >= 0 ? '+' : '' ?><?= number_format($earningsChangePercent, 1) ?>%
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <a href="<?= Url::to(['/earnings/index']) ?>" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm expense-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="expense-icon me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-arrow-down-circle text-danger" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v5.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V4.5z"/>
                            </svg>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Расходы</h6>
                            <h3 class="mb-0">
                                <?php
                                $expensesCurrency = $defaultCurrency ?? Currency::findByCode('RUB');
                                echo $expensesCurrency ? $expensesCurrency->formatAmount($periodExpenses ?? 0) : Yii::$app->formatter->asCurrency($periodExpenses ?? 0, 'RUB');
                                ?>
                            </h3>
                            <?php if (isset($expensesChangePercent) && $expensesChangePercent != 0): ?>
                                <small class="<?= $expensesChangePercent <= 0 ? 'text-success' : 'text-danger' ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-arrow-<?= $expensesChangePercent <= 0 ? 'down' : 'up' ?>" viewBox="0 0 16 16">
                                        <?php if ($expensesChangePercent <= 0): ?>
                                            <path fill-rule="evenodd" d="M8 1a.5.5 0 0 1 .5.5v11.793l3.146-3.147a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 .708-.708L7.5 13.293V1.5A.5.5 0 0 1 8 1z"/>
                                        <?php else: ?>
                                            <path fill-rule="evenodd" d="M8 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L7.5 2.707V14.5A.5.5 0 0 0 8 15z"/>
                                        <?php endif; ?>
                                    </svg>
                                    <?= $expensesChangePercent >= 0 ? '+' : '' ?><?= number_format($expensesChangePercent, 1) ?>%
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <a href="<?= Url::to(['/expenses/index']) ?>" class="stretched-link"></a>
            </div>
        </div>
    </div>
</div>

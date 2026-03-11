<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $totalBalance float */
/* @var $totalBalanceChange float */
/* @var $totalBalanceChangePercent float */
/* @var $defaultCurrency app\models\Currency\Currency|null */
/* @var $accounts app\models\Account\Account[] */
/* @var $periodEarnings float */
/* @var $periodExpenses float */
/* @var $earningsChangePercent float */
/* @var $expensesChangePercent float */

$this->title = 'Главная';
?>
<div class="site-index">
    <?php if (!Yii::$app->user->isGuest): ?>
        <!-- Блок 1: Общий баланс -->
        <?= $this->render('_block_balance', [
            'totalBalance' => $totalBalance,
            'totalBalanceChange' => $totalBalanceChange,
            'totalBalanceChangePercent' => $totalBalanceChangePercent,
            'defaultCurrency' => $defaultCurrency,
        ]) ?>

        <!-- Блок 2: Балансы по счетам -->
        <?= $this->render('_block_accounts', [
            'accounts' => $accounts,
        ]) ?>

        <!-- Блок 3: Доходы / Расходы за период -->
        <?= $this->render('_block_income_expense', [
            'periodEarnings' => $periodEarnings,
            'periodExpenses' => $periodExpenses,
            'earningsChangePercent' => $earningsChangePercent,
            'expensesChangePercent' => $expensesChangePercent,
            'defaultCurrency' => $defaultCurrency,
        ]) ?>

        <!-- Блок 4: График статистики -->
        <?= $this->render('_block_statistics') ?>
    <?php else: ?>
        <!-- Контент для гостей -->
        <?php // TODO Пока заглушка, в будущем сделать демонстрационные данные ?>
        <div class="hero-section">
            <div class="container h-100">
                <div class="row h-100 align-items-center justify-content-center text-center">
                    <div class="col-lg-8">
                        <h1 class="display-4 mb-4">Добро пожаловать в Finance</h1>
                        <p class="lead mb-0">Управляйте своими финансами эффективно и просто</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>


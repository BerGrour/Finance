<?php

use app\models\Currency\Currency;
use yii\helpers\Url;

/**
 * Частичное представление блока "Общий баланс"
 * 
 * @var float $totalBalance Общий баланс
 * @var float $totalBalanceChange Изменение баланса
 * @var float $totalBalanceChangePercent Процент изменения баланса
 * @var \app\models\Currency\Currency|null $defaultCurrency Валюта по умолчанию
 */
?>
<div class="balance-block card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h6 class="text-muted mb-2">Общий баланс</h6>
                <h2 class="mb-1">
                    <?php
                    if (!$defaultCurrency) {
                        $defaultCurrency = Currency::findByCode('RUB');
                    }
                    echo $defaultCurrency ? $defaultCurrency->formatAmount($totalBalance) : Yii::$app->formatter->asCurrency($totalBalance, 'RUB');
                    ?>
                </h2>
                <?php if (isset($totalBalanceChange) && $totalBalanceChange != 0): ?>
                    <div class="balance-change">
                        <?php
                        $changeClass = $totalBalanceChange >= 0 ? 'text-success' : 'text-danger';
                        $changeSign = $totalBalanceChange >= 0 ? '+' : '';
                        $changePercentSign = $totalBalanceChangePercent >= 0 ? '+' : '';
                        ?>
                        <span class="<?= $changeClass ?>">
                            <?= $changeSign ?><?= $defaultCurrency ? $defaultCurrency->formatAmount($totalBalanceChange) : Yii::$app->formatter->asCurrency($totalBalanceChange, 'RUB') ?>
                        </span>
                        <?php if ($totalBalanceChangePercent != 0): ?>
                            <span class="<?= $changeClass ?>">
                                (<?= $changePercentSign ?><?= number_format($totalBalanceChangePercent, 1) ?>%)
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <a href="<?= Url::to(['/accounts/index']) ?>" class="stretched-link"></a>
</div>

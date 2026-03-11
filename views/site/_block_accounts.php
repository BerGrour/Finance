<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Account\Account;

/**
 * Частичное представление блока "Мои счета"
 * 
 * @var \app\models\Account\Account[] $accounts Массив счетов
 */
?>
<div class="accounts-block mb-4">
    <h5 class="mb-3">Мои счета</h5>
    <div class="row g-3">
        <?php if (empty($accounts)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    У вас пока нет счетов. <?= Html::a('Создать счет', ['/accounts/create']) ?>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($accounts as $account): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card account-card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="account-icon me-3">
                                    <?php
                                    $iconClass = 'bi-wallet2';
                                    if ($account->type === Account::TYPE_DEBIT_CARD) {
                                        $iconClass = 'bi-credit-card';
                                    } elseif ($account->type === Account::TYPE_CREDIT_CARD) {
                                        $iconClass = 'bi-credit-card-2-front';
                                    }
                                    ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi <?= $iconClass ?>" viewBox="0 0 16 16">
                                        <?php if ($iconClass === 'bi-wallet2'): ?>
                                            <path d="M12.136.326A1.5 1.5 0 0 1 14 1.78V3h.5A1.5 1.5 0 0 1 16 4.5v9a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 13.5v-9a1.5 1.5 0 0 1 1.432-1.499L12.136.326zM5.562 3H13V1.78a.5.5 0 0 0-.621-.484L5.562 3zM1.5 4a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h13a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5h-13z"/>
                                        <?php elseif ($iconClass === 'bi-credit-card'): ?>
                                            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1H2zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7z"/>
                                            <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1z"/>
                                        <?php else: ?>
                                            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1H2zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7z"/>
                                            <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1z"/>
                                        <?php endif; ?>
                                    </svg>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?= Html::encode($account->name) ?></h6>
                                    <small class="text-muted"><?= $account->getTypeName() ?></small>
                                </div>
                            </div>
                            <div class="account-balance">
                                <h4 class="mb-0">
                                    <?= $account->currency ? $account->currency->formatAmount($account->balance) : Yii::$app->formatter->asCurrency($account->balance, 'RUB') ?>
                                </h4>
                            </div>
                        </div>
                        <a href="<?= Url::to(['/accounts/view', 'id' => $account->id]) ?>" class="stretched-link"></a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

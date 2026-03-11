<?php
declare(strict_types=1);

namespace app\models\traits;

use app\models\Account\Account;

/**
 * Трейт для обновления баланса счета
 * 
 * Предоставляет:
 * - Отслеживание старых значений ($_oldAmount, $_oldStatus, $_oldAccountId)
 * - Методы для сохранения старых значений (afterFindAccountBalance, beforeSaveAccountBalance)
 * - Метод обновления баланса счета (updateAccountBalance)
 * 
 * Требует наличия в модели:
 * - свойства $account_id
 * - свойства $amount
 * - свойства $status
 * - константы STATUS_RECEIVED
 */
trait AccountBalanceTrait
{
    /**
     * Старое значение суммы
     * @var float|null
     */
    private ?float $_oldAmount = null;
    
    /**
     * Старое значение статуса
     * @var int|null
     */
    private ?int $_oldStatus = null;
    
    /**
     * Старое значение account_id
     * @var int|null
     */
    private ?int $_oldAccountId = null;

    /**
     * После поиска - сохраняем старые значения
     * 
     * ВАЖНО: Этот метод должен быть вызван в afterFind() модели
     */
    protected function afterFindAccountBalance(): void
    {
        $this->_oldAmount = $this->amount !== null ? (float)$this->amount : null;
        $this->_oldStatus = $this->status !== null ? (int)$this->status : null;
        $this->_oldAccountId = $this->account_id !== null ? (int)$this->account_id : null;
    }

    /**
     * Перед сохранением - записываем старые значения
     *
     * @param bool $insert
     * @return bool
     */
    protected function beforeSaveAccountBalance(bool $insert): bool
    {
        if (!$insert) {
            $oldAmount = $this->getOldAttribute('amount');
            $oldStatus = $this->getOldAttribute('status');
            $oldAccountId = $this->getOldAttribute('account_id');

            $this->_oldAmount = $oldAmount !== null ? (float)$oldAmount : null;
            $this->_oldStatus = $oldStatus !== null ? (int)$oldStatus : null;
            $this->_oldAccountId = $oldAccountId !== null ? (int)$oldAccountId : null;
        }
        return true;
    }


    /**
     * Обновление баланса счета
     *
     * @param int $account_id ID счета
     * @param float $amount Сумма
     * @param string $operation Операция: 'add' или 'subtract'
     */
    protected function updateAccountBalance(int $account_id, float $amount, string $operation = 'add'): void
    {
        $account = Account::findOne($account_id);
        if ($account) {
            if ($operation === 'add') {
                $account->balance += $amount;
            } else {
                $account->balance -= $amount;
            }
            $account->save(false, ['balance', 'updated_at']);
        }
    }
}

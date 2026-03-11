<?php
declare(strict_types=1);

namespace app\models\Transfers;

use Yii;
use app\models\Expenses\Expenses;
use app\models\Earnings\Earnings;
use app\models\Transfers\Transfers;
use app\models\Transfers\TransfersExpenses;
use app\models\Transfers\TransfersEarnings;
use app\models\Earnings\EarningsSource;
use app\models\Account\Account;
use yii\base\Model;

/**
 * Форма для создания перевода между счетами
 */
class TransferForm extends Model
{
    /**
     * @var Expenses Трата на счете отправителя
     */
    public ?Expenses $expense = null;

    /**
     * @var int ID счета получателя
     */
    public ?int $transfer_account_id = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['transfer_account_id'], 'required'],
            [['transfer_account_id'], 'integer'],
            [['transfer_account_id'], 'validateTransferAccount'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'transfer_account_id' => 'Счет получателя',
        ];
    }

    /**
     * Валидация счета получателя
     *
     * @param string $attribute
     * @param ?array $params
     */
    public function validateTransferAccount(string $attribute, ?array $params): void
    {
        if (!$this->expense || !$this->expense->account_id) {
            $this->addError($attribute, 'Не указан счет отправителя');
            return;
        }

        if ($this->transfer_account_id == $this->expense->account_id) {
            $this->addError($attribute, 'Счет получателя не может совпадать со счетом отправителя');
            return;
        }

        $account = Account::findByUser()
            ->andWhere(['id' => $this->transfer_account_id])
            ->one();

        if (!$account) {
            $this->addError($attribute, 'Выбранный счет получателя не найден или недоступен');
        }
    }

    /**
     * Создание перевода
     *
     * @return bool
     * @throws \Exception
     */
    public function createTransfer(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Создаем запись о переводе
            $transfer = new Transfers();
            $transfer->user_id = Yii::$app->user->id;
            $transfer->account_id = $this->transfer_account_id;
            $transfer->amount = $this->expense->amount;
            $transfer->status = $this->expense->status === Expenses::STATUS_RECEIVED
                ? Transfers::STATUS_COMPLETED
                : Transfers::STATUS_PENDING;

            if (!$transfer->save()) {
                throw new \Exception('Ошибка при создании записи о переводе: ' . implode(', ', $transfer->getFirstErrors()));
            }
            
            $transferExpense = new TransfersExpenses();
            $transferExpense->transfer_id = $transfer->id;
            $transferExpense->expense_id = $this->expense->id;
            if (!$transferExpense->save()) {
                throw new \Exception('Ошибка при связывании перевода с тратой: ' . implode(', ', $transferExpense->getFirstErrors()));
            }

            $this->createTransferEarning($transfer);

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->addError('transfer_account_id', $e->getMessage());
            Yii::error('Ошибка при создании перевода: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Создание связи transfer и earning
     * @param Transfers $transfer
     * @throws \Exception
     */
    public function createTransferEarning(Transfers $transfer): void
    {
        $transferSource = $this->getOrCreateTransferSource();
        if (!$transferSource) {
            throw new \Exception('Ошибка при получении источника заработка "Переводы"');
        }

        $earning = $this->createEarning($transferSource);
        if (!$earning->save()) {
            throw new \Exception('Ошибка при создании записи о заработке: ' . implode(', ', $earning->getFirstErrors()));
        }

        $transferEarning = new TransfersEarnings();
        $transferEarning->transfer_id = $transfer->id;
        $transferEarning->earning_id = $earning->id;
        if (!$transferEarning->save()) {
            throw new \Exception('Ошибка при связывании перевода с заработком: ' . implode(', ', $transferEarning->getFirstErrors()));
        }
    }

    /**
     * Получение или создание источника заработка "Переводы"
     *
     * @return EarningsSource|null
     */
    protected function getOrCreateTransferSource(): ?EarningsSource
    {
        $transferSource = EarningsSource::findByUser()
            ->andWhere([
                'name' => 'Переводы',
                'type' => EarningsSource::TYPE_TRANSFER
            ])->one();

        if (empty($transferSource)) {
            $transferSource = new EarningsSource();
            $transferSource->user_id = Yii::$app->user->id;
            $transferSource->name = 'Переводы';
            $transferSource->type = EarningsSource::TYPE_TRANSFER;
            $transferSource->is_static = true;
            if (!$transferSource->save()) {
                return null;
            }
        } elseif ($transferSource->is_deleted == 1) {
            $transferSource->restore();
        }

        return $transferSource;
    }

    /**
     * Создание записи о заработке
     *
     * @param EarningsSource $source
     * @return Earnings
     */
    protected function createEarning(EarningsSource $source): Earnings
    {
        $earning = new Earnings();
        $earning->user_id = Yii::$app->user->id;
        $earning->account_id = $this->transfer_account_id;
        $earning->date_time = $this->expense->date_time;
        $earning->amount = $this->expense->amount;
        $earning->category = Earnings::CATEGORY_OTHER;
        $earning->source_id = $source->id;
        $earning->status = $this->expense->status;
        $earning->comment = 'Перевод со счета: ' . ($this->expense->account->name ?? '');

        return $earning;
    }
}

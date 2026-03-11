<?php
declare(strict_types=1);

namespace app\models\Earnings;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;
use app\models\traits\DateTimeTrait;
use app\models\traits\CategoryTrait;
use app\models\traits\AccountBalanceTrait;
use app\models\traits\SoftDeleteTrait;
use app\models\traits\UserTrackingTrait;
use app\models\Account\Account;
use app\models\User\User;
use app\models\Earnings\EarningsSource;
use app\models\Expenses\Expenses;
use app\models\Transfers\Transfers;
use app\models\Transfers\TransfersEarnings;
use app\models\Transfers\TransfersExpenses;

/**
 * Модель заработка
 *
 * @property int $id
 * @property int $user_id ID пользователя
 * @property int $account_id ID счета/кошелька
 * @property int $date_time Дата и время заработка (timestamp)
 * @property float $amount Сумма заработка
 * @property int $category Категория заработка
 * @property int $source_id ID источника заработка
 * @property int $status Статус заработка (planned, received, cancelled)
 * @property string|null $comment Комментарий
 * @property int $created_at Дата создания
 * @property int $updated_at Дата обновления
 * @property int $is_counted_in_stats Учитывать в статистике (1 — да, 0 — нет)
 * @property int $is_deleted Признак мягкого удаления
 * @property int|null $deleted_at Дата мягкого удаления
 *
 * @property User $user Связь с пользователем
 * @property Account $account Связь со счетом
 * @property EarningsSource $source Связь с источником заработка
 */
class Earnings extends ActiveRecord
{
    use DateTimeTrait;
    use CategoryTrait;
    use AccountBalanceTrait;
    use SoftDeleteTrait;
    use UserTrackingTrait;

    // Статусы заработка
    const STATUS_PLANNED = 1;    // Запланирован
    const STATUS_RECEIVED = 2;   // Получен
    const STATUS_CANCELLED = 3;  // Отменен

    // Категории заработка (можно расширить по необходимости)
    const CATEGORY_SALARY = 1;           // Зарплата
    const CATEGORY_BONUS = 2;            // Премия
    const CATEGORY_FREELANCE = 3;        // Фриланс
    const CATEGORY_INVESTMENT = 4;       // Инвестиции
    const CATEGORY_SALE = 5;             // Продажа
    const CATEGORY_OTHER = 99;           // Прочее

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%earnings}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'account_id', 'date_time', 'amount', 'category', 'source_id', 'status'], 'required'],
            [['user_id', 'account_id', 'date_time', 'category', 'source_id', 'status', 'created_at', 'updated_at', 'is_deleted', 'deleted_at', 'is_counted_in_stats'], 'integer'],
            [['amount'], 'number', 'min' => 0.01],
            [['category'], 'in', 'range' => array_keys(self::getCategoryList())],
            [['status'], 'in', 'range' => [self::STATUS_PLANNED, self::STATUS_RECEIVED, self::STATUS_CANCELLED]],
            [['comment'], 'string'],
            [['is_deleted'], 'default', 'value' => 0],
            [['is_counted_in_stats'], 'default', 'value' => 1],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['account_id'], 'exist', 'skipOnError' => true, 'targetClass' => Account::class, 'targetAttribute' => ['account_id' => 'id']],
            [['source_id'], 'exist', 'skipOnError' => true, 'targetClass' => EarningsSource::class, 'targetAttribute' => ['source_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'Пользователь',
            'account_id' => 'Счет',
            'date_time' => 'Дата и время',
            'amount' => 'Сумма',
            'category' => 'Категория',
            'source_id' => 'Источник',
            'status' => 'Статус',
            'comment' => 'Комментарий',
            'is_counted_in_stats' => 'Учитывать в статистике',
            'created_at' => 'Создан',
            'updated_at' => 'Обновлен',
        ];
    }

    /**
     * После поиска - сохраняем старые значения
     */
    public function afterFind(): void
    {
        parent::afterFind();
        $this->afterFindAccountBalance();
    }

    /**
     * Поиск только активных записей (без удалённых).
     *
     * @return \app\models\Earnings\EarningsQuery
     */
    public static function find(): EarningsQuery
    {
        return (new EarningsQuery(static::class))->notDeleted();
    }

    /**
     * Поиск с учётом удалённых записей.
     *
     * @return \app\models\Earnings\EarningsQuery
     */
    public static function findDeleted(): EarningsQuery
    {
        return (new EarningsQuery(static::class))->deleted();
    }

    /**
     * Поиск только активных записей текущего пользователя
     *
     * @return ActiveQuery
     */
    public static function findByUser(): EarningsQuery
    {
        return (new EarningsQuery(static::class))->notDeleted()->byUser();
    }

    /**
     * Перед сохранением - проверка наличия даты и сохранение старых значений
     *
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->ensureUserId();

        if (!$this->validateDateTime()) {
            return false;
        }

        // Сохранение старых значений для обновления баланса
        return $this->beforeSaveAccountBalance($insert);
    }

    /**
     * После сохранения - обновление баланса счета
     *
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        // Обновляем баланс только для полученных заработков
        if ($this->status === self::STATUS_RECEIVED) {
            if ($insert) {
                // Новый заработок - добавляем к балансу
                $this->updateAccountBalance($this->account_id, $this->amount, 'add');
            } else {
                // Обновление учитываем изменения
                $oldAccountId = $this->_oldAccountId;
                $oldAmount = $this->_oldAmount;
                $oldStatus = $this->_oldStatus;
                
                if ($oldAccountId != $this->account_id) {
                    // Убираем из старого счета (если был получен)
                    if ($oldStatus === self::STATUS_RECEIVED) {
                        $this->updateAccountBalance($oldAccountId, $oldAmount, 'subtract');
                    }
                    // Добавляем к новому счету
                    $this->updateAccountBalance($this->account_id, $this->amount, 'add');
                } else {
                    // Счет не изменился, но могла измениться сумма или статус
                    if ($oldStatus === self::STATUS_RECEIVED) {
                        // Старый статус был "получен" - убираем старую сумму
                        $this->updateAccountBalance($this->account_id, $oldAmount, 'subtract');
                    }
                    // Добавляем новую сумму
                    $this->updateAccountBalance($this->account_id, $this->amount, 'add');
                }
            }
        } else {
            // Статус не "получен" - если это обновление и раньше был "получен", убираем из баланса
            if (!$insert && $this->_oldStatus === self::STATUS_RECEIVED) {
                $this->updateAccountBalance($this->_oldAccountId, $this->_oldAmount, 'subtract');
            }
        }
    }

    /**
     * После удаления - обновление баланса счета
     */
    public function afterDelete(): void
    {
        parent::afterDelete();
        
        if ($this->status === self::STATUS_RECEIVED) {
            $this->updateAccountBalance($this->account_id, (float) $this->amount, 'subtract');
        }
    }

    /**
     * Удаление поступления. Если это поступление от перевода — каскадно удаляет
     * связанную трату, промежуточные записи и запись Transfers.
     *
     * @return false|int
     */
    public function delete(): false|int
    {
        $transferEarning = TransfersEarnings::findOne(['earning_id' => $this->id]);
        if (!$transferEarning) {
            return parent::delete();
        }

        $transaction = static::getDb()->beginTransaction();
        try {
            $this->cascadeDeleteTransfer($transferEarning);
            $result = parent::delete();
            $transaction->commit();
            return $result;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error('Ошибка каскадного удаления поступления-перевода #' . $this->id . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Каскадное удаление сущностей перевода, связанных с данным поступлением.
     * Пивот-записи удаляются первыми, чтобы разорвать связь и предотвратить рекурсию.
     *
     * @param TransfersEarnings $transferEarning
     * @throws \Throwable
     */
    private function cascadeDeleteTransfer(TransfersEarnings $transferEarning): void
    {
        $transfer_id = $transferEarning->transfer_id;

        $transferExpense = TransfersExpenses::findOne(['transfer_id' => $transfer_id]);
        if ($transferExpense) {
            $expense_id = $transferExpense->expense_id;
            $transferExpense->delete();

            $expense = Expenses::findOne($expense_id);
            if ($expense) {
                $expense->delete();
            }
        }

        $transferEarning->delete();

        $transfer = Transfers::findOne($transfer_id);
        if ($transfer) {
            $transfer->delete();
        }
    }

    /**
     * Получение списка статусов
     *
     * @return array
     */
    public static function getStatusList(): array
    {
        return [
            self::STATUS_PLANNED => 'Запланирован',
            self::STATUS_RECEIVED => 'Получен',
            self::STATUS_CANCELLED => 'Отменен',
        ];
    }

    /**
     * Получение названия статуса
     *
     * @return string
     */
    public function getStatusName(): string
    {
        $statuses = self::getStatusList();
        return $statuses[$this->status] ?? 'Неизвестно';
    }

    /**
     * Получение списка категорий
     *
     * @return array
     */
    public static function getCategoryList(): array
    {
        return [
            self::CATEGORY_SALARY => 'Зарплата',
            self::CATEGORY_BONUS => 'Премия',
            self::CATEGORY_FREELANCE => 'Фриланс',
            self::CATEGORY_INVESTMENT => 'Инвестиции',
            self::CATEGORY_SALE => 'Продажа',
            self::CATEGORY_OTHER => 'Прочее',
        ];
    }

    /**
     * Связь с пользователем
     *
     * @return ActiveQuery
     */
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Связь со счетом
     *
     * @return ActiveQuery
     */
    public function getAccount(): ActiveQuery
    {
        return $this->hasOne(Account::class, ['id' => 'account_id']);
    }

    /**
     * Связь с источником заработка
     *
     * @return ActiveQuery
     */
    public function getSource(): ActiveQuery
    {
        return $this->hasOne(EarningsSource::class, ['id' => 'source_id']);
    }
}

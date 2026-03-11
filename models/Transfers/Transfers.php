<?php
declare(strict_types=1);

namespace app\models\Transfers;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;
use app\models\Transfers\TransfersExpenses;
use app\models\Transfers\TransfersEarnings;
use app\models\Transfers\TransfersQuery;
use app\models\traits\SoftDeleteTrait;
use app\models\traits\UserTrackingTrait;
use app\models\User\User;
use app\models\Account\Account;
use app\models\Expenses\Expenses;
use app\models\Earnings\Earnings;

/**
 * Модель переводов между счетами
 *
 * @property int $id
 * @property int $user_id ID пользователя
 * @property int $account_id ID счета получателя
 * @property float $amount Сумма перевода
 * @property int $status Статус перевода
 * @property int $created_at Дата создания
 * @property int $updated_at Дата обновления
 * @property int $is_deleted Признак мягкого удаления
 * @property int|null $deleted_at Дата мягкого удаления
 *
 * @property User $user Связь с пользователем
 * @property Account $account Связь со счетом получателя
 * @property Expenses $expense Связь с тратой (через transfers_expenses)
 * @property Earnings $earning Связь с заработком (через transfers_earnings)
 */
class Transfers extends ActiveRecord
{
    use SoftDeleteTrait;
    use UserTrackingTrait;

    // Статусы переводов
    const STATUS_PENDING = 1;    // Ожидает выполнения
    const STATUS_COMPLETED = 2;   // Выполнен
    const STATUS_CANCELLED = 3;   // Отменен

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%transfers}}';
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
            [['user_id', 'account_id', 'amount', 'status'], 'required'],
            [['user_id', 'account_id', 'status', 'created_at', 'updated_at', 'is_deleted', 'deleted_at'], 'integer'],
            [['amount'], 'number', 'min' => 0.01],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_COMPLETED, self::STATUS_CANCELLED]],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['account_id'], 'exist', 'skipOnError' => true, 'targetClass' => Account::class, 'targetAttribute' => ['account_id' => 'id']],
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
            'account_id' => 'Счет получателя',
            'amount' => 'Сумма',
            'status' => 'Статус',
            'created_at' => 'Создан',
            'updated_at' => 'Обновлен',
        ];
    }

    /**
     * Получение списка статусов
     *
     * @return array
     */
    public static function getStatusList(): array
    {
        return [
            self::STATUS_PENDING => 'Ожидает выполнения',
            self::STATUS_COMPLETED => 'Выполнен',
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

    public static function find(): TransfersQuery
    {
        return (new TransfersQuery(static::class))->notDeleted();
    }

    public static function findDeleted(): TransfersQuery
    {
        return (new TransfersQuery(static::class))->deleted();
    }

    /**
     * Поиск только активных записей текущего пользователя
     *
     * @return ActiveQuery
     */
    public static function findByUser(): TransfersQuery
    {
        return (new TransfersQuery(static::class))->notDeleted()->byUser();
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
     * Связь со счетом получателя
     *
     * @return ActiveQuery
     */
    public function getAccount(): ActiveQuery
    {
        return $this->hasOne(Account::class, ['id' => 'account_id']);
    }

    /**
     * Связь с таблицей transfers_expenses
     *
     * @return ActiveQuery
     */
    public function getTransfersExpenses(): ActiveQuery
    {
        return $this->hasOne(TransfersExpenses::class, ['transfer_id' => 'id']);
    }

    /**
     * Связь с таблицей transfers_earnings
     *
     * @return ActiveQuery
     */
    public function getTransfersEarnings(): ActiveQuery
    {
        return $this->hasOne(TransfersEarnings::class, ['transfer_id' => 'id']);
    }

    /**
     * Связь с тратой через промежуточную таблицу
     *
     * @return ActiveQuery
     */
    public function getExpense(): ActiveQuery
    {
        return $this->hasOne(Expenses::class, ['id' => 'expense_id'])
            ->via('transfersExpenses');
    }

    /**
     * Связь с заработком через промежуточную таблицу
     *
     * @return ActiveQuery
     */
    public function getEarning(): ActiveQuery
    {
        return $this->hasOne(Earnings::class, ['id' => 'earning_id'])
            ->via('transfersEarnings');
    }
}

<?php
declare(strict_types=1);

namespace app\models\Expenses;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;
use app\models\Expenses\ExpensesQuery;
use app\models\traits\DateTimeTrait;
use app\models\traits\CategoryTrait;
use app\models\traits\AccountBalanceTrait;
use app\models\traits\SoftDeleteTrait;
use app\models\traits\UserTrackingTrait;
use app\models\Account\Account;
use app\models\User\User;
use app\models\Earnings\Earnings;
use app\models\Transfers\Transfers;
use app\models\Transfers\TransfersExpenses;
use app\models\Transfers\TransfersEarnings;

/**
 * Модель трат
 *
 * @property int $id
 * @property int $user_id ID пользователя
 * @property int $account_id ID счета/кошелька
 * @property int $date_time Дата и время траты (timestamp)
 * @property float $amount Сумма траты
 * @property int $category Категория траты
 * @property int $status Статус траты (planned, received, cancelled)
 * @property string|null $comment Комментарий
 * @property int $created_at Дата создания
 * @property int $updated_at Дата обновления
 * @property int $is_counted_in_stats Учитывать в статистике (1 — да, 0 — нет)
 * @property int $is_deleted Признак мягкого удаления
 * @property int|null $deleted_at Дата мягкого удаления
 *
 * @property User $user Связь с пользователем
 * @property Account $account Связь со счетом
 */
class Expenses extends ActiveRecord
{
    use DateTimeTrait;
    use CategoryTrait;
    use AccountBalanceTrait;
    use SoftDeleteTrait;
    use UserTrackingTrait;

    // Статусы трат
    const STATUS_PLANNED = 1;    // Запланирована
    const STATUS_RECEIVED = 2;   // Совершена
    const STATUS_CANCELLED = 3;  // Отменена

    // Категории трат
    const CATEGORY_TRANSFER = 1;        // Переводы (особая категория для переводов между счетами)
    const CATEGORY_FOOD = 2;            // Продукты
    const CATEGORY_TRANSPORT = 3;       // Транспорт
    const CATEGORY_HOUSING = 4;         // Жилье
    const CATEGORY_ENTERTAINMENT = 5;   // Развлечения
    const CATEGORY_HEALTH = 6;          // Здоровье
    const CATEGORY_CLOTHING = 7;        // Одежда
    const CATEGORY_EDUCATION = 8;       // Образование
    const CATEGORY_UTILITIES = 9;       // Коммунальные услуги
    const CATEGORY_SHOPPING = 10;       // Покупки
    const CATEGORY_RESTAURANTS = 11;    // Рестораны
    const CATEGORY_SUBS = 12;           // Подписки
    const CATEGORY_OTHER = 99;          // Прочее

    /**
     * Виртуальное свойство для счета получателя при переводе
     * @var int|null
     */
    public $transfer_account_id;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%expenses}}';
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
            [['user_id', 'account_id', 'date_time', 'amount', 'category', 'status'], 'required'],
            [['user_id', 'account_id', 'date_time', 'category', 'status', 'created_at', 'updated_at', 'is_deleted', 'deleted_at', 'is_counted_in_stats'], 'integer'],
            [['amount'], 'number', 'min' => 0.01],
            [['category'], 'in', 'range' => array_keys(self::getCategoryList())],
            [['status'], 'in', 'range' => [self::STATUS_PLANNED, self::STATUS_RECEIVED, self::STATUS_CANCELLED]],
            [['comment'], 'string'],
            [['is_deleted'], 'default', 'value' => 0],
            [['is_counted_in_stats'], 'default', 'value' => 1],
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
            'account_id' => 'Счет',
            'date_time' => 'Дата и время',
            'amount' => 'Сумма',
            'category' => 'Категория',
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
     * @return \app\models\Expenses\ExpensesQuery
     */
    public static function find(): ExpensesQuery
    {
        return (new ExpensesQuery(static::class))->notDeleted();
    }

    /**
     * Поиск с учётом удалённых записей.
     *
     * @return \app\models\Expenses\ExpensesQuery
     */
    public static function findDeleted(): ExpensesQuery
    {
        return (new ExpensesQuery(static::class))->deleted();
    }

    /**
     * Поиск только активных записей текущего пользователя
     *
     * @return ActiveQuery
     */
    public static function findByUser(): ExpensesQuery
    {
        return (new ExpensesQuery(static::class))->notDeleted()->byUser();
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
        
        // Обновляем баланс только для совершенных трат
        if ($this->status === self::STATUS_RECEIVED) {
            if ($insert) {
                // Новая трата - вычитаем из баланса
                $this->updateAccountBalance($this->account_id, $this->amount, 'subtract');
            } else {
                // Обновление учитываем изменения
                $oldAccountId = $this->_oldAccountId;
                $oldAmount = $this->_oldAmount;
                $oldStatus = $this->_oldStatus;
                
                // Если изменился счет
                if ($oldAccountId != $this->account_id) {
                    // Возвращаем в старый счет (если была совершена)
                    if ($oldStatus === self::STATUS_RECEIVED) {
                        $this->updateAccountBalance($oldAccountId, $oldAmount, 'add');
                    }
                    // Вычитаем из нового счета
                    $this->updateAccountBalance($this->account_id, $this->amount, 'subtract');
                } else {
                    // Счет не изменился, но могла измениться сумма или статус
                    if ($oldStatus === self::STATUS_RECEIVED) {
                        // Старый статус был "совершена" - возвращаем старую сумму
                        $this->updateAccountBalance($this->account_id, $oldAmount, 'add');
                    }
                    // Вычитаем новую сумму
                    $this->updateAccountBalance($this->account_id, $this->amount, 'subtract');
                }
            }
        } else {
            // Статус не "совершена" - если это обновление и раньше была "совершена", возвращаем в баланс
            if (!$insert && $this->_oldStatus === self::STATUS_RECEIVED) {
                $this->updateAccountBalance($this->_oldAccountId, $this->_oldAmount, 'add');
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
            $this->updateAccountBalance($this->account_id, (float) $this->amount, 'add');
        }
    }

    /**
     * Удаление траты. Если это перевод — каскадно удаляет связанные сущности:
     * поступление, промежуточные записи и запись Transfers.
     *
     * @return false|int
     */
    public function delete(): false|int
    {
        if ($this->category !== self::CATEGORY_TRANSFER) {
            return parent::delete();
        }

        $transaction = static::getDb()->beginTransaction();
        try {
            $this->cascadeDeleteTransfer();
            $result = parent::delete();
            $transaction->commit();
            return $result;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error('Ошибка каскадного удаления траты-перевода #' . $this->id . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Каскадное удаление сущностей перевода, связанных с данной тратой.
     * Пивот-записи удаляются первыми, чтобы разорвать связь и предотвратить рекурсию.
     *
     * @throws \Throwable
     */
    private function cascadeDeleteTransfer(): void
    {
        $transferExpense = TransfersExpenses::findOne(['expense_id' => $this->id]);
        if (!$transferExpense) {
            return;
        }

        $transfer_id = $transferExpense->transfer_id;

        $transferEarning = TransfersEarnings::findOne(['transfer_id' => $transfer_id]);
        if ($transferEarning) {
            $earning_id = $transferEarning->earning_id;
            $transferEarning->delete();

            $earning = Earnings::findOne($earning_id);
            if ($earning) {
                $earning->delete();
            }
        }

        $transferExpense->delete();

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
            self::STATUS_PLANNED => 'Запланирована',
            self::STATUS_RECEIVED => 'Совершена',
            self::STATUS_CANCELLED => 'Отменена',
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
            self::CATEGORY_TRANSFER => 'Переводы',
            self::CATEGORY_FOOD => 'Продукты',
            self::CATEGORY_TRANSPORT => 'Транспорт',
            self::CATEGORY_HOUSING => 'Жилье',
            self::CATEGORY_ENTERTAINMENT => 'Развлечения',
            self::CATEGORY_HEALTH => 'Здоровье',
            self::CATEGORY_CLOTHING => 'Одежда',
            self::CATEGORY_EDUCATION => 'Образование',
            self::CATEGORY_UTILITIES => 'Коммунальные услуги',
            self::CATEGORY_SHOPPING => 'Покупки',
            self::CATEGORY_RESTAURANTS => 'Рестораны',
            self::CATEGORY_SUBS => 'Подписки',        
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
}

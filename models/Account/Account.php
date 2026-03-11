<?php
declare(strict_types=1);

namespace app\models\Account;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;
use app\models\traits\SoftDeleteTrait;
use app\models\traits\UserTrackingTrait;
use app\models\User\User;
use app\models\Currency\Currency;
use app\models\Earnings\Earnings;
use app\models\Earnings\EarningsQuery;
use app\models\Expenses\Expenses;
use app\models\Expenses\ExpensesQuery;

/**
 * Модель счета/кошелька
 *
 * @property int $id
 * @property int $user_id ID пользователя
 * @property string $name Наименование счета
 * @property string $type Тип счета (cash, debit_card, credit_card)
 * @property int $currency_id ID валюты
 * @property float $balance Баланс счета
 * @property float $initial_balance Начальный баланс счета
 * @property bool $is_deleted Удален ли счет
 * @property bool $is_default Счет по умолчанию
 * @property string|null $comment Комментарий
 * @property int $created_at Дата создания
 * @property int $updated_at Дата обновления
 * @property int|null $deleted_at Дата удаления (soft delete)
 *
 * @property User $user Связь с пользователем
 * @property Currency $currency Связь с валютой
 */
class Account extends ActiveRecord
{
    use SoftDeleteTrait;
    use UserTrackingTrait;

    // Типы счетов
    const TYPE_CASH = 'cash';
    const TYPE_DEBIT_CARD = 'debit_card';
    const TYPE_CREDIT_CARD = 'credit_card';

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%account}}';
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
            [['user_id', 'name', 'type', 'currency_id'], 'required'],
            [['user_id', 'currency_id', 'created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 50],
            [['type'], 'in', 'range' => [self::TYPE_CASH, self::TYPE_DEBIT_CARD, self::TYPE_CREDIT_CARD]],
            [['balance', 'initial_balance'], 'number'],
            [['initial_balance'], 'default', 'value' => 0.00],
            [['is_deleted', 'is_default'], 'boolean'],
            [['is_deleted', 'is_default'], 'default', 'value' => false],
            [['comment'], 'string'],
            [['user_id', 'name'], 'unique', 'targetAttribute' => ['user_id', 'name'], 
                'filter' => ['deleted_at' => null],
                'message' => 'У вас уже есть счет с таким названием'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::class, 'targetAttribute' => ['currency_id' => 'id']],
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
            'name' => 'Наименование',
            'type' => 'Тип счета',
            'currency_id' => 'Валюта',
            'balance' => 'Баланс',
            'initial_balance' => 'Начальный баланс',
            'is_deleted' => 'Удален',
            'is_default' => 'По умолчанию',
            'comment' => 'Комментарий',
            'created_at' => 'Создан',
            'updated_at' => 'Обновлен',
            'deleted_at' => 'Удален',
        ];
    }

    /**
     * Поиск только активных записей
     *
     * @return ActiveQuery
     */
    public static function find(): AccountQuery
    {
        return (new AccountQuery(static::class))->notDeleted();
    }

    /**
     * Поиск с учетом удалённых записей (без фильтра is_deleted).
     */
    public static function findDeleted(): AccountQuery
    {
        return (new AccountQuery(static::class))->deleted();
    }

    /**
     * Поиск только активных записей текущего пользователя
     *
     * @return ActiveQuery
     */
    public static function findByUser(): AccountQuery
    {
        return (new AccountQuery(static::class))->notDeleted()->byUser();
    }

    /**
     * Получение списка типов счетов
     *
     * @return array
     */
    public static function getTypeList(): array
    {
        return [
            self::TYPE_CASH => 'Наличные',
            self::TYPE_DEBIT_CARD => 'Дебетовая карта',
            self::TYPE_CREDIT_CARD => 'Кредитная карта',
        ];
    }

    /**
     * Получение названия типа счета
     *
     * @return string
     */
    public function getTypeName(): string
    {
        $types = self::getTypeList();
        return $types[$this->type] ?? $this->type;
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
     * Связь с валютой
     *
     * @return ActiveQuery
     */
    public function getCurrency(): ActiveQuery
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    /**
     * Перед сохранением - проверка is_default и установка баланса
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

        // Если устанавливается как счет по умолчанию, снимаем флаг с других счетов пользователя
        if ($this->is_default) {
            static::updateAll(
                ['is_default' => false],
                ['user_id' => $this->user_id, 'deleted_at' => null]
            );
        }

        if ($insert && $this->balance == 0 && ($this->initial_balance ?? 0) != 0) {
            $this->balance = $this->initial_balance;
        }

        return true;
    }

    /**
     * Пересчет баланса счета на основе заработков и трат
     *
     * @return bool
     */
    public function recalculateBalance(): bool
    {
        // Сумма всех полученных заработков
        /** @var EarningsQuery $earningsQuery */
        $earningsQuery = Earnings::findByUser();
        $earningsSum = $earningsQuery
            ->andWhere([
                'account_id' => $this->id,
                'status' => Earnings::STATUS_RECEIVED
            ])
            ->sum('amount') ?: 0;

        // Сумма всех совершенных трат
        /** @var ExpensesQuery $expensesQuery */
        $expensesQuery = Expenses::findByUser();
        $expensesSum = $expensesQuery
            ->andWhere([
                'account_id' => $this->id,
                'status' => Expenses::STATUS_RECEIVED
            ])
            ->sum('amount') ?: 0;

        // Баланс = начальный баланс + заработки - траты
        $this->balance = ($this->initial_balance ?? 0) + $earningsSum - $expensesSum;
        
        return $this->save(false, ['balance', 'updated_at']);
    }

    /**
     * Получение суммы заработков за период
     *
     * @param int|null $startTimestamp Начало периода (timestamp)
     * @param int|null $endTimestamp Конец периода (timestamp)
     * @return float
     */
    public function getEarningsSum(?int $startTimestamp = null, ?int $endTimestamp = null): float
    {
        /** @var EarningsQuery $query */
        $query = Earnings::findByUser()
            ->andWhere([
                'account_id' => $this->id,
                'status' => Earnings::STATUS_RECEIVED
            ]);
        
        if ($startTimestamp !== null) {
            $query->andWhere(['>=', 'date_time', $startTimestamp]);
        }
        
        if ($endTimestamp !== null) {
            $query->andWhere(['<=', 'date_time', $endTimestamp]);
        }
        
        return $query->sum('amount') ?: 0;
    }

    /**
     * Получение суммы трат за период
     *
     * @param int|null $startTimestamp Начало периода (timestamp)
     * @param int|null $endTimestamp Конец периода (timestamp)
     * @return float
     */
    public function getExpensesSum(?int $startTimestamp = null, ?int $endTimestamp = null): float
    {
        /** @var ExpensesQuery $query */
        $query = Expenses::findByUser()
            ->andWhere([
                'account_id' => $this->id,
                'status' => Expenses::STATUS_RECEIVED
            ]);
        
        if ($startTimestamp !== null) {
            $query->andWhere(['>=', 'date_time', $startTimestamp]);
        }
        
        if ($endTimestamp !== null) {
            $query->andWhere(['<=', 'date_time', $endTimestamp]);
        }
        
        return $query->sum('amount') ?: 0;
    }
}

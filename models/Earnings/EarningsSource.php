<?php
declare(strict_types=1);

namespace app\models\Earnings;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;
use app\models\Earnings\EarningsSourceQuery;
use app\models\traits\SoftDeleteTrait;
use app\models\traits\UserTrackingTrait;
use app\models\User\User;
use yii\helpers\ArrayHelper;

/**
 * Модель источника заработка
 *
 * @property int $id
 * @property int $user_id ID пользователя
 * @property string $name Наименование источника
 * @property int $type Тип источника (работа, фриланс, продажа)
 * @property bool $is_deleted Удален ли источник
 * @property bool $is_static Системная запись (нельзя редактировать и удалять)
 * @property int $created_at Дата создания
 * @property int $updated_at Дата обновления
 * @property int|null $deleted_at Дата удаления (soft delete)
 *
 * @property User $user Связь с пользователем
 * @property Earnings[] $earnings Связь с заработками
 */
class EarningsSource extends ActiveRecord
{
    use SoftDeleteTrait {
        softDelete as private traitSoftDelete;
    }
    use UserTrackingTrait;

    // Типы источников заработка
    const TYPE_WORK = 1;      // Работа
    const TYPE_FREELANCE = 2; // Фриланс
    const TYPE_SALE = 3;      // Продажа
    const TYPE_TRANSFER = 4;  // Перевод между счетами

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%earnings_source}}';
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
            [['user_id', 'name', 'type'], 'required'],
            [['user_id', 'type', 'created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['is_deleted'], 'boolean'],
            [['is_deleted'], 'default', 'value' => false],
            [['is_static'], 'boolean'],
            [['is_static'], 'default', 'value' => false],
            [['user_id', 'name'], 'unique', 'targetAttribute' => ['user_id', 'name'],
                'filter' => ['deleted_at' => null],
                'message' => 'У вас уже есть источник с таким названием'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
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
            'type' => 'Тип источника',
            'is_deleted' => 'Удален',
            'is_static' => 'Системный',
            'created_at' => 'Создан',
            'updated_at' => 'Обновлен',
            'deleted_at' => 'Удален',
        ];
    }

    /**
     * Является ли запись системной (нельзя редактировать и удалять).
     */
    public function isStatic(): bool
    {
        return (bool)$this->is_static;
    }

    /**
     * Мягкое удаление. Системные записи удалению не подлежат.
     */
    public function softDelete(): bool
    {
        if ($this->isStatic()) {
            Yii::error("Попытка удаления системного источника заработка {$this->name}", __METHOD__);
            $this->addError('is_static', 'Системный источник нельзя удалить.');
            return false;
        }

        return $this->traitSoftDelete();
    }

    /**
     * Поиск только активных записей
     *
     * @return ActiveQuery
     */
    public static function find(): EarningsSourceQuery
    {
        return (new EarningsSourceQuery(static::class))->notDeleted();
    }

    public static function findDeleted(): EarningsSourceQuery
    {
        return (new EarningsSourceQuery(static::class))->deleted();
    }

    /**
     * Поиск только активных записей текущего пользователя
     *
     * @return ActiveQuery
     */
    public static function findByUser(): EarningsSourceQuery
    {
        return (new EarningsSourceQuery(static::class))->notDeleted()->byUser();
    }

    /**
     * Получение списка типов источников
     *
     * @return array
     */
    public static function getTypeList(): array
    {
        return [
            self::TYPE_WORK => 'Работа',
            self::TYPE_FREELANCE => 'Фриланс',
            self::TYPE_SALE => 'Продажа',
        ];
    }

    /**
     * Получение названия типа источника
     *
     * @return string
     */
    public function getTypeName(): string
    {
        $types = self::getTypeList();
        return $types[$this->type] ?? 'Неизвестно';
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
     * Связь с заработками
     *
     * @return ActiveQuery
     */
    public function getEarnings(): ActiveQuery
    {
        return $this->hasMany(Earnings::class, ['source_id' => 'id']);
    }

    /**
     * Список источников заработка для выбора в форме
     * @return array
     */
    public static function toList(): array
    {
        return ArrayHelper::map(
            self::findByUser()->orderBy(['name' => SORT_ASC])->all(),
            'id',
            'name'
        );
    }
}

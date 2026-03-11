<?php
declare(strict_types=1);

namespace app\models\Currency;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\models\traits\SoftDeleteTrait;

/**
 * Модель валюты (справочник)
 *
 * @property int $id
 * @property string $code ISO-код валюты
 * @property string $name Человекочитаемое название
 * @property string $symbol Символ валюты
 * @property int $precision Количество знаков после запятой
 * @property bool $is_deleted Удалена ли валюта
 * @property int $sort_order Порядок сортировки
 * @property int $created_at Дата создания
 * @property int $updated_at Дата обновления
 */
class Currency extends ActiveRecord
{
    use SoftDeleteTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%currency}}';
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
            [['code', 'name', 'symbol'], 'required'],
            [['code'], 'string', 'max' => 10],
            [['code'], 'unique'],
            [['code'], 'match', 'pattern' => '/^[A-Z]{3}$/', 'message' => 'Код валюты должен состоять из 3 заглавных букв'],
            [['name'], 'string', 'max' => 64],
            [['symbol'], 'string', 'max' => 8],
            [['precision'], 'integer', 'min' => 0, 'max' => 8],
            [['precision'], 'default', 'value' => 2],
            [['is_deleted'], 'boolean'],
            [['is_deleted'], 'default', 'value' => false],
            [['sort_order'], 'integer'],
            [['sort_order'], 'default', 'value' => 100],
            [['created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'code' => 'Код валюты',
            'name' => 'Название',
            'symbol' => 'Символ',
            'precision' => 'Точность',
            'is_deleted' => 'Удалена',
            'sort_order' => 'Порядок сортировки',
            'created_at' => 'Создана',
            'updated_at' => 'Обновлена',
        ];
    }

    /**
     * Поиск только активных валют
     *
     * @return \yii\db\ActiveQuery
     */
    public static function find(): CurrencyQuery
    {
        return (new CurrencyQuery(static::class))->notDeleted();
    }

    public static function findDeleted(): CurrencyQuery
    {
        return (new CurrencyQuery(static::class))->deleted();
    }

    /**
     * Получение списка валют для выпадающего списка
     *
     * @return array Массив [id => название]
     */
    public static function getList(): array
    {
        return static::find()
            ->select(['name', 'id'])
            ->indexBy('id')
            ->column();
    }

    /**
     * Получение списка валют с кодом для выпадающего списка
     *
     * @return array Массив [id => 'Код - Название']
     */
    public static function getListWithCode(): array
    {
        $currencies = static::find()->all();
        $list = [];
        foreach ($currencies as $currency) {
            $list[$currency->id] = $currency->code . ' - ' . $currency->name;
        }
        return $list;
    }

    /**
     * Получение валюты по коду
     *
     * @param string $code ISO-код валюты
     * @return static|null
     */
    public static function findByCode(string $code): ?self
    {
        return static::findOne(['code' => $code, 'is_deleted' => false]);
    }

    /**
     * Форматирование суммы в валюте
     *
     * @param float $amount Сумма
     * @return string Отформатированная строка
     */
    public function formatAmount(float $amount): string
    {
        return number_format($amount, $this->precision, '.', ' ') . ' ' . $this->symbol;
    }
}

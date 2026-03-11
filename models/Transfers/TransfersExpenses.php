<?php
declare(strict_types=1);

namespace app\models\Transfers;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;
use app\models\Expenses\Expenses;

/**
 * Модель связи переводов и трат
 *
 * @property int $id
 * @property int $transfer_id ID перевода
 * @property int $expense_id ID траты
 * @property int $created_at Дата создания
 *
 * @property Transfers $transfer Связь с переводом
 * @property Expenses $expense Связь с тратой
 */
class TransfersExpenses extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%transfers_expenses}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['transfer_id', 'expense_id'], 'required'],
            [['transfer_id', 'expense_id', 'created_at'], 'integer'],
            [['transfer_id'], 'unique'],
            [['transfer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Transfers::class, 'targetAttribute' => ['transfer_id' => 'id']],
            [['expense_id'], 'exist', 'skipOnError' => true, 'targetClass' => Expenses::class, 'targetAttribute' => ['expense_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'transfer_id' => 'ID перевода',
            'expense_id' => 'ID траты',
            'created_at' => 'Создан',
        ];
    }

    /**
     * Связь с переводом
     *
     * @return ActiveQuery
     */
    public function getTransfer(): ActiveQuery
    {
        return $this->hasOne(Transfers::class, ['id' => 'transfer_id']);
    }

    /**
     * Связь с тратой
     *
     * @return ActiveQuery
     */
    public function getExpense(): ActiveQuery
    {
        return $this->hasOne(Expenses::class, ['id' => 'expense_id']);
    }
}

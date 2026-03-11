<?php
declare(strict_types=1);

namespace app\models\Transfers;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;
use app\models\Earnings\Earnings;

/**
 * Модель связи переводов и заработков
 *
 * @property int $id
 * @property int $transfer_id ID перевода
 * @property int $earning_id ID заработка
 * @property int $created_at Дата создания
 *
 * @property Transfers $transfer Связь с переводом
 * @property Earnings $earning Связь с заработком
 */
class TransfersEarnings extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%transfers_earnings}}';
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
            [['transfer_id', 'earning_id'], 'required'],
            [['transfer_id', 'earning_id', 'created_at'], 'integer'],
            [['transfer_id'], 'unique'],
            [['transfer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Transfers::class, 'targetAttribute' => ['transfer_id' => 'id']],
            [['earning_id'], 'exist', 'skipOnError' => true, 'targetClass' => Earnings::class, 'targetAttribute' => ['earning_id' => 'id']],
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
            'earning_id' => 'ID заработка',
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
     * Связь с заработком
     *
     * @return ActiveQuery
     */
    public function getEarning(): ActiveQuery
    {
        return $this->hasOne(Earnings::class, ['id' => 'earning_id']);
    }
}

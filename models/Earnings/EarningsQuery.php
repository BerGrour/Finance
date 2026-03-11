<?php
declare(strict_types=1);

namespace app\models\Earnings;

use Yii;
use yii\db\ActiveQuery;

/**
 * Запрос для модели Earnings с поддержкой soft delete.
 */
class EarningsQuery extends ActiveQuery
{
    public function notDeleted(): self
    {
        return $this->andWhere(['is_deleted' => 0]);
    }

    public function deleted(): self
    {
        return $this->andWhere(['is_deleted' => 1]);
    }

    public function byUser(): self
    {
        return $this->andWhere(['user_id' => Yii::$app->user->id]);
    }
}


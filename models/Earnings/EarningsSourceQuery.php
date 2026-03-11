<?php
declare(strict_types=1);

namespace app\models\Earnings;

use Yii;
use yii\db\ActiveQuery;

/**
 * Запрос для модели EarningsSource с поддержкой soft delete.
 */
class EarningsSourceQuery extends ActiveQuery
{
    /**
     * Только не удалённые записи.
     */
    public function notDeleted(): self
    {
        return $this->andWhere(['is_deleted' => 0]);
    }

    /**
     * Только удалённые записи.
     */
    public function deleted(): self
    {
        return $this->andWhere(['is_deleted' => 1]);
    }

    /**
     * Для текущего пользователя
     */
    public function byUser(): self
    {
        return $this->andWhere(['user_id' => Yii::$app->user->id]);
    }
}


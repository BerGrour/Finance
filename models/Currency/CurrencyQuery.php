<?php
declare(strict_types=1);

namespace app\models\Currency;

use yii\db\ActiveQuery;

/**
 * Запрос для модели Currency с поддержкой soft delete.
 */
class CurrencyQuery extends ActiveQuery
{
    /**
     * Только не удалённые валюты.
     */
    public function notDeleted(): self
    {
        return $this->andWhere(['is_deleted' => 0]);
    }

    /**
     * Только удалённые валюты.
     */
    public function deleted(): self
    {
        return $this->andWhere(['is_deleted' => 1]);
    }
}


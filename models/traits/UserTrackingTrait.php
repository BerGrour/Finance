<?php
declare(strict_types=1);

namespace app\models\traits;

use Yii;

/**
 * Трейт для автоматического проставления user_id.
 *
 * Требует наличия колонки user_id INT NOT NULL в таблице.
 */
trait UserTrackingTrait
{
    /**
     * Устанавливает user_id из текущего авторизованного пользователя,
     * если значение ещё не задано.
     */
    protected function ensureUserId(): void
    {
        if ($this->getAttribute('user_id')) {
            return;
        }

        if (Yii::$app->user->isGuest) {
            return;
        }

        $this->setAttribute('user_id', (int) Yii::$app->user->id);
    }
}


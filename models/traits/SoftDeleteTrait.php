<?php
declare(strict_types=1);

namespace app\models\traits;

use Yii;

/**
 * Трейт для реализации мягкого удаления записей.
 *
 * Требует наличия в таблице полей:
 * - is_deleted TINYINT(1) NOT NULL DEFAULT 0
 * - deleted_at INT NULL
 *
 * Ожидается, что модель наследуется от yii\db\ActiveRecord.
 */
trait SoftDeleteTrait
{
    /**
     * Выполнить мягкое удаление записи.
     *
     * @return bool
     */
    public function softDelete(): bool
    {
        $this->setAttribute('is_deleted', 1);
        $this->setAttribute('deleted_at', time());

        return $this->save(false, ['is_deleted', 'deleted_at']);
    }

    /**
     * Восстановить ранее мягко удалённую запись.
     *
     * @return bool
     */
    public function restore(): bool
    {
        $this->setAttribute('is_deleted', 0);
        $this->setAttribute('deleted_at', null);

        return $this->save(false, ['is_deleted', 'deleted_at']);
    }
}


<?php
declare(strict_types=1);

namespace app\models\traits;

/**
 * Трейт для работы с категориями
 * 
 * Требует наличия метода getCategoryList() в классе
 */
trait CategoryTrait
{
    /**
     * Получение названия категории
     *
     * @return string
     */
    public function getCategoryName(): string
    {
        $categories = static::getCategoryList();
        return $categories[$this->category] ?? 'Неизвестно';
    }
}

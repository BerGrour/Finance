<?php
declare(strict_types=1);

namespace app\models\traits;

/**
 * Трейт для работы с датой и временем
 */
trait DateTimeTrait
{
    /**
     * Получение даты в формате Y-m-d
     *
     * @return string
     */
    public function getDate(): string
    {
        return $this->date_time ? date('Y-m-d', $this->date_time) : '';
    }

    /**
     * Получение времени в формате H:i
     *
     * @return string
     */
    public function getTime(): string
    {
        return $this->date_time ? date('H:i', $this->date_time) : '';
    }

    /**
     * Получение списка времени с интервалом 15 минут
     *
     * @return array
     */
    public static function getTimeList(): array
    {
        $times = [];
        for ($hour = 0; $hour < 24; $hour++) {
            for ($minute = 0; $minute < 60; $minute += 15) {
                $time = sprintf('%02d:%02d', $hour, $minute);
                $times[$time] = $time;
            }
        }
        return $times;
    }

    /**
     * Установка даты и времени из строковых значений
     *
     * @param string $date Дата в формате Y-m-d
     * @param string|null $time Время в формате H:i (необязательно, по умолчанию 00:00)
     * @return bool
     */
    public function setDateTime(string $date, ?string $time = null): bool
    {
        if (empty($date)) {
            return false;
        }
        
        if (empty($time)) {
            $time = '00:00';
        }
        
        $datetime = strtotime($date . ' ' . $time);
        if ($datetime === false) {
            return false;
        }
        
        $this->date_time = $datetime;
        return true;
    }

    /**
     * Валидация даты и времени перед сохранением
     *
     * @return bool
     */
    protected function validateDateTime(): bool
    {
        if (empty($this->date_time)) {
            $this->addError('date_time', 'Дата обязательна для заполнения');
            return false;
        }
        return true;
    }
}

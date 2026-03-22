<?php

declare(strict_types=1);

namespace app\models\User;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Модель пользовательских настроек.
 * Один ряд на пользователя. Новые параметры — новые колонки.
 *
 * @property int $id
 * @property int $user_id
 * @property string $theme
 * @property int $created_at
 * @property int $updated_at
 */
class UserPreferences extends ActiveRecord
{
    public const THEME_LIGHT = 'light';
    public const THEME_DARK  = 'dark';

    public static function tableName(): string
    {
        return '{{%user_preferences}}';
    }

    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules(): array
    {
        return [
            ['user_id', 'required'],
            ['user_id', 'integer'],
            ['theme', 'required'],
            ['theme', 'in', 'range' => [self::THEME_LIGHT, self::THEME_DARK]],
            ['theme', 'string', 'max' => 20],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id'         => 'ID',
            'user_id'    => 'Пользователь',
            'theme'      => 'Тема оформления',
            'created_at' => 'Создан',
            'updated_at' => 'Обновлён',
        ];
    }

    /**
     * Возвращает запись настроек для пользователя или создаёт новую с дефолтными значениями.
     */
    public static function findOrCreate(int $userId): self
    {
        $prefs = static::findOne(['user_id' => $userId]);

        if ($prefs === null) {
            $prefs = new self();
            $prefs->user_id = $userId;
            $prefs->theme   = self::THEME_LIGHT;
        }

        return $prefs;
    }

    /**
     * Устанавливает и сохраняет тему пользователя.
     */
    public function setTheme(string $theme): bool
    {
        if (!in_array($theme, [self::THEME_LIGHT, self::THEME_DARK], true)) {
            return false;
        }

        $this->theme = $theme;

        return $this->save();
    }
}

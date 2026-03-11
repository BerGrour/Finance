<?php

use yii\db\Migration;

/**
 * Добавляет поле is_static в таблицу earnings_source
 * и создаёт системную запись «Переводы» для каждого существующего пользователя.
 */
class m260228_130621_add_is_static_to_earnings_source extends Migration
{
    private const TYPE_TRANSFER = 4;
    private const SOURCE_NAME   = 'Переводы';

    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->addColumn(
            '{{%earnings_source}}',
            'is_static',
            $this->tinyInteger(1)->notNull()->defaultValue(0)->after('type')
        );

        $this->createIndex(
            'idx-earnings_source-is_static',
            '{{%earnings_source}}',
            'is_static'
        );

        $this->markExistingTransferSourcesAsStatic();
        $this->insertMissingTransferSources();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropIndex('idx-earnings_source-is_static', '{{%earnings_source}}');
        $this->dropColumn('{{%earnings_source}}', 'is_static');
    }

    /**
     * Помечает уже существующие источники типа «Переводы» как системные.
     */
    private function markExistingTransferSourcesAsStatic(): void
    {
        $this->update(
            '{{%earnings_source}}',
            ['is_static' => 1],
            ['type' => self::TYPE_TRANSFER, 'name' => self::SOURCE_NAME]
        );
    }

    /**
     * Для пользователей, у которых ещё нет источника «Переводы», создаёт его.
     */
    private function insertMissingTransferSources(): void
    {
        $userIds = $this->db
            ->createCommand('SELECT [[id]] FROM {{%user}}')
            ->queryColumn();

        if (empty($userIds)) {
            return;
        }

        $existingUserIds = $this->db
            ->createCommand(
                'SELECT [[user_id]] FROM {{%earnings_source}} '
                . 'WHERE [[type]] = :type AND [[name]] = :name',
                [':type' => self::TYPE_TRANSFER, ':name' => self::SOURCE_NAME]
            )
            ->queryColumn();

        $missingUserIds = array_diff($userIds, $existingUserIds);

        $now = time();
        foreach ($missingUserIds as $userId) {
            $this->insert('{{%earnings_source}}', [
                'user_id'    => $userId,
                'name'       => self::SOURCE_NAME,
                'type'       => self::TYPE_TRANSFER,
                'is_static'  => 1,
                'is_deleted' => 0,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);
        }
    }
}

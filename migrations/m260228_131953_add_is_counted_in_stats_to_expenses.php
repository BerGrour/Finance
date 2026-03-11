<?php

use yii\db\Migration;

/**
 * Добавляет поле is_counted_in_stats в таблицу expenses.
 * По умолчанию 1 (учитывать в статистике).
 */
class m260228_131953_add_is_counted_in_stats_to_expenses extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn(
            '{{%expenses}}',
            'is_counted_in_stats',
            $this->tinyInteger(1)->notNull()->defaultValue(1)->after('comment')->comment('Учитывать в статистике')
        );

        $this->createIndex(
            'idx-expenses-is_counted_in_stats',
            '{{%expenses}}',
            'is_counted_in_stats'
        );
    }

    public function safeDown(): void
    {
        $this->dropIndex('idx-expenses-is_counted_in_stats', '{{%expenses}}');
        $this->dropColumn('{{%expenses}}', 'is_counted_in_stats');
    }
}

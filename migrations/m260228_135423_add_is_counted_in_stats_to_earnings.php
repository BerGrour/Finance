<?php

use yii\db\Migration;

/**
 * Добавляет поле is_counted_in_stats в таблицу earnings.
 * По умолчанию 1 (учитывать в статистике).
 */
class m260228_135423_add_is_counted_in_stats_to_earnings extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn(
            '{{%earnings}}',
            'is_counted_in_stats',
            $this->tinyInteger(1)->notNull()->defaultValue(1)->after('comment')->comment('Учитывать в статистике')
        );

        $this->createIndex(
            'idx-earnings-is_counted_in_stats',
            '{{%earnings}}',
            'is_counted_in_stats'
        );
    }

    public function safeDown(): void
    {
        $this->dropIndex('idx-earnings-is_counted_in_stats', '{{%earnings}}');
        $this->dropColumn('{{%earnings}}', 'is_counted_in_stats');
    }
}

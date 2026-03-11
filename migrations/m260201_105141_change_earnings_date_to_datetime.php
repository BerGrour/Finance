<?php

use yii\db\Migration;

/**
 * Миграция для изменения поля date на date_time (int) в таблице earnings
 */
class m260201_105141_change_earnings_date_to_datetime extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex('idx-earnings-date', '{{%earnings}}');
        $this->dropColumn('{{%earnings}}', 'date');

        $this->addColumn('{{%earnings}}', 'date_time', $this->integer()->notNull()->after('account_id'));
        $this->createIndex('idx-earnings-date_time', '{{%earnings}}', 'date_time');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-earnings-date_time', '{{%earnings}}');
        $this->dropColumn('{{%earnings}}', 'date_time');
        
        $this->addColumn('{{%earnings}}', 'date', $this->date()->notNull()->after('account_id'));
        $this->createIndex('idx-earnings-date', '{{%earnings}}', 'date');
    }
}

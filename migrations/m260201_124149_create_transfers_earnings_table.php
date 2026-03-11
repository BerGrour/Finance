<?php

use yii\db\Migration;

/**
 * Миграция для создания таблицы связи переводов и заработков
 */
class m260201_124149_create_transfers_earnings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%transfers_earnings}}', [
            'id' => $this->primaryKey(),
            'transfer_id' => $this->integer()->notNull(),
            'earning_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ], 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB');

        $this->addForeignKey(
            'fk-transfers_earnings-transfer_id',
            '{{%transfers_earnings}}',
            'transfer_id',
            '{{%transfers}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-transfers_earnings-earning_id',
            '{{%transfers_earnings}}',
            'earning_id',
            '{{%earnings}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx-transfers_earnings-earning_id', '{{%transfers_earnings}}', 'earning_id');
        $this->createIndex('idx-transfers_earnings-transfer_id', '{{%transfers_earnings}}', 'transfer_id', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-transfers_earnings-transfer_id', '{{%transfers_earnings}}');
        $this->dropForeignKey('fk-transfers_earnings-earning_id', '{{%transfers_earnings}}');
        $this->dropTable('{{%transfers_earnings}}');
    }
}

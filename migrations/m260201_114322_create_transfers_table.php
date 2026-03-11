<?php

use yii\db\Migration;

/**
 * Миграция для создания таблицы переводов
 */
class m260201_114322_create_transfers_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%transfers}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'account_id' => $this->integer()->notNull(),
            'amount' => $this->decimal(15, 2)->notNull(),
            'status' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB');

        $this->addForeignKey(
            'fk-transfers-user_id',
            '{{%transfers}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-transfers-account_id',
            '{{%transfers}}',
            'account_id',
            '{{%account}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx-transfers-user_id', '{{%transfers}}', 'user_id');
        $this->createIndex('idx-transfers-account_id', '{{%transfers}}', 'account_id');
        $this->createIndex('idx-transfers-status', '{{%transfers}}', 'status');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-transfers-user_id', '{{%transfers}}');
        $this->dropForeignKey('fk-transfers-account_id', '{{%transfers}}');
        $this->dropTable('{{%transfers}}');
    }
}

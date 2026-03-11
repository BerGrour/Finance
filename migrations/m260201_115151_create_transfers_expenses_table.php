<?php

use yii\db\Migration;

/**
 * Миграция для создания таблицы связи переводов и трат
 */
class m260201_115151_create_transfers_expenses_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%transfers_expenses}}', [
            'id' => $this->primaryKey(),
            'transfer_id' => $this->integer()->notNull(),
            'expense_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ], 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB');

        $this->addForeignKey(
            'fk-transfers_expenses-transfer_id',
            '{{%transfers_expenses}}',
            'transfer_id',
            '{{%transfers}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-transfers_expenses-expense_id',
            '{{%transfers_expenses}}',
            'expense_id',
            '{{%expenses}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx-transfers_expenses-expense_id', '{{%transfers_expenses}}', 'expense_id');
        $this->createIndex('idx-transfers_expenses-transfer_id', '{{%transfers_expenses}}', 'transfer_id', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-transfers_expenses-transfer_id', '{{%transfers_expenses}}');
        $this->dropForeignKey('fk-transfers_expenses-expense_id', '{{%transfers_expenses}}');
        $this->dropTable('{{%transfers_expenses}}');
    }
}

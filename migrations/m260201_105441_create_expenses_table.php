<?php

use yii\db\Migration;

/**
 * Миграция для создания таблицы трат
 */
class m260201_105441_create_expenses_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%expenses}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'account_id' => $this->integer()->notNull(),
            'date_time' => $this->integer()->notNull(),
            'amount' => $this->decimal(15, 2)->notNull(),
            'category' => $this->tinyInteger()->notNull(),
            'status' => $this->integer()->notNull(),
            'comment' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB');

        $this->addForeignKey(
            'fk-expenses-user_id',
            '{{%expenses}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-expenses-account_id',
            '{{%expenses}}',
            'account_id',
            '{{%account}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx-expenses-user_id', '{{%expenses}}', 'user_id');
        $this->createIndex('idx-expenses-account_id', '{{%expenses}}', 'account_id');
        $this->createIndex('idx-expenses-date_time', '{{%expenses}}', 'date_time');
        $this->createIndex('idx-expenses-category', '{{%expenses}}', 'category');
        $this->createIndex('idx-expenses-status', '{{%expenses}}', 'status');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-expenses-user_id', '{{%expenses}}');
        $this->dropForeignKey('fk-expenses-account_id', '{{%expenses}}');
        $this->dropTable('{{%expenses}}');
    }
}

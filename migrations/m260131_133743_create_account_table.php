<?php

use yii\db\Migration;

/**
 * Миграция для создания таблицы счетов/кошельков
 */
class m260131_133743_create_account_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%account}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull(),
            'type' => $this->string(50)->notNull(),
            'currency_id' => $this->integer()->notNull(),
            'balance' => $this->decimal(15, 2)->notNull()->defaultValue(0.00),
            'is_deleted' => $this->boolean()->notNull()->defaultValue(false),
            'is_default' => $this->boolean()->notNull()->defaultValue(false),
            'comment' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'deleted_at' => $this->integer()->null(),
        ], 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB');

        $this->addForeignKey(
            'fk-account-user_id',
            '{{%account}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx-account-user_id', '{{%account}}', 'user_id');
        $this->createIndex('idx-account-type', '{{%account}}', 'type');
        $this->createIndex('idx-account-currency_id', '{{%account}}', 'currency_id');
        $this->createIndex('idx-account-is_deleted', '{{%account}}', 'is_deleted');
        $this->createIndex('idx-account-is_default', '{{%account}}', 'is_default');

        $this->createIndex('idx-account-user_name', '{{%account}}', ['user_id', 'name']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-account-user_id', '{{%account}}');

        $this->dropTable('{{%account}}');
    }
}

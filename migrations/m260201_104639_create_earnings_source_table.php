<?php

use yii\db\Migration;

/**
 * Миграция для создания таблицы источников заработка
 */
class m260201_104639_create_earnings_source_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%earnings_source}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull(),
            'type' => $this->tinyInteger()->notNull(),
            'is_deleted' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'deleted_at' => $this->integer()->null(),
        ], 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB');

        $this->addForeignKey(
            'fk-earnings_source-user_id',
            '{{%earnings_source}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx-earnings_source-user_id', '{{%earnings_source}}', 'user_id');
        $this->createIndex('idx-earnings_source-type', '{{%earnings_source}}', 'type');
        $this->createIndex('idx-earnings_source-is_deleted', '{{%earnings_source}}', 'is_deleted');
        $this->createIndex('idx-earnings_source-user_name', '{{%earnings_source}}', ['user_id', 'name']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-earnings_source-user_id', '{{%earnings_source}}');
        $this->dropTable('{{%earnings_source}}');
    }
}

<?php

use yii\db\Migration;

/**
 * Миграция для создания таблицы заработка
 */
class m260201_104812_create_earnings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%earnings}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'account_id' => $this->integer()->notNull(),
            'date' => $this->date()->notNull(),
            'amount' => $this->decimal(15, 2)->notNull(),
            'category' => $this->tinyInteger()->notNull(),
            'source_id' => $this->integer()->notNull(),
            'status' => $this->integer()->notNull(),
            'comment' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB');

        $this->addForeignKey(
            'fk-earnings-user_id',
            '{{%earnings}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-earnings-account_id',
            '{{%earnings}}',
            'account_id',
            '{{%account}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-earnings-source_id',
            '{{%earnings}}',
            'source_id',
            '{{%earnings_source}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx-earnings-user_id', '{{%earnings}}', 'user_id');
        $this->createIndex('idx-earnings-account_id', '{{%earnings}}', 'account_id');
        $this->createIndex('idx-earnings-source_id', '{{%earnings}}', 'source_id');
        $this->createIndex('idx-earnings-date', '{{%earnings}}', 'date');
        $this->createIndex('idx-earnings-category', '{{%earnings}}', 'category');
        $this->createIndex('idx-earnings-status', '{{%earnings}}', 'status');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-earnings-user_id', '{{%earnings}}');
        $this->dropForeignKey('fk-earnings-account_id', '{{%earnings}}');
        $this->dropForeignKey('fk-earnings-source_id', '{{%earnings}}');
        $this->dropTable('{{%earnings}}');
    }
}

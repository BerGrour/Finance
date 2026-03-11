<?php

use yii\db\Migration;

/**
 * Миграция для создания таблицы валют (справочник)
 */
class m260131_155416_create_currency_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%currency}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(10)->notNull()->unique(),
            'name' => $this->string(64)->notNull(),
            'symbol' => $this->string(8)->notNull(),
            'precision' => $this->tinyInteger()->notNull()->defaultValue(2),
            'is_deleted' => $this->boolean()->notNull()->defaultValue(false),
            'sort_order' => $this->integer()->notNull()->defaultValue(100),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB');

        $this->createIndex('idx-currency-code', '{{%currency}}', 'code');
        $this->createIndex('idx-currency-is_deleted', '{{%currency}}', 'is_deleted');
        $this->createIndex('idx-currency-sort_order', '{{%currency}}', 'sort_order');

        $currencies = [
            ['code' => 'RUB', 'name' => 'Российский рубль', 'symbol' => '₽', 'precision' => 2, 'sort_order' => 1],
            ['code' => 'USD', 'name' => 'Доллар США', 'symbol' => '$', 'precision' => 2, 'sort_order' => 2],
            ['code' => 'EUR', 'name' => 'Евро', 'symbol' => '€', 'precision' => 2, 'sort_order' => 3],
            ['code' => 'GBP', 'name' => 'Фунт стерлингов', 'symbol' => '£', 'precision' => 2, 'sort_order' => 4],
            ['code' => 'CNY', 'name' => 'Китайский юань', 'symbol' => '¥', 'precision' => 2, 'sort_order' => 5],
            ['code' => 'JPY', 'name' => 'Японская иена', 'symbol' => '¥', 'precision' => 0, 'sort_order' => 6],
            ['code' => 'KZT', 'name' => 'Казахстанский тенге', 'symbol' => '₸', 'precision' => 2, 'sort_order' => 7],
            ['code' => 'BYN', 'name' => 'Белорусский рубль', 'symbol' => 'Br', 'precision' => 2, 'sort_order' => 8],
        ];

        $timestamp = time();
        foreach ($currencies as $currency) {
            $this->insert('{{%currency}}', array_merge($currency, [
                'is_deleted' => false,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%currency}}');
    }
}

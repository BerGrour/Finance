<?php

use yii\db\Migration;

/**
 * Миграция для добавления внешнего ключа на currency_id в таблице account
 */
class m260131_161348_add_currency_foreign_key extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'fk-account-currency_id',
            '{{%account}}',
            'currency_id',
            '{{%currency}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-account-currency_id', '{{%account}}');
    }
}

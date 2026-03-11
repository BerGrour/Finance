<?php

use yii\db\Migration;

/**
 * Миграция для добавления поля initial_balance в таблицу account
 */
class m260201_105730_add_initial_balance_to_account extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%account}}', 'initial_balance', $this->decimal(15, 2)->notNull()->defaultValue(0.00)->after('balance'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%account}}', 'initial_balance');
    }
}

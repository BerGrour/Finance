<?php

use yii\db\Migration;

/**
 * Добавление полей soft delete к основным операциям:
 * - earnings
 * - expenses
 * - transfers
 */
class m260217_121938_add_soft_delete_to_operations extends Migration
{
    public function safeUp(): void
    {
        // earnings
        $this->addColumn('{{%earnings}}', 'is_deleted', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn('{{%earnings}}', 'deleted_at', $this->integer()->null());
        $this->createIndex('idx-earnings-is_deleted', '{{%earnings}}', 'is_deleted');

        // expenses
        $this->addColumn('{{%expenses}}', 'is_deleted', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn('{{%expenses}}', 'deleted_at', $this->integer()->null());
        $this->createIndex('idx-expenses-is_deleted', '{{%expenses}}', 'is_deleted');

        // transfers
        $this->addColumn('{{%transfers}}', 'is_deleted', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn('{{%transfers}}', 'deleted_at', $this->integer()->null());
        $this->createIndex('idx-transfers-is_deleted', '{{%transfers}}', 'is_deleted');
    }

    public function safeDown(): void
    {
        // earnings
        $this->dropIndex('idx-earnings-is_deleted', '{{%earnings}}');
        $this->dropColumn('{{%earnings}}', 'is_deleted');
        $this->dropColumn('{{%earnings}}', 'deleted_at');

        // expenses
        $this->dropIndex('idx-expenses-is_deleted', '{{%expenses}}');
        $this->dropColumn('{{%expenses}}', 'is_deleted');
        $this->dropColumn('{{%expenses}}', 'deleted_at');

        // transfers
        $this->dropIndex('idx-transfers-is_deleted', '{{%transfers}}');
        $this->dropColumn('{{%transfers}}', 'is_deleted');
        $this->dropColumn('{{%transfers}}', 'deleted_at');
    }
}


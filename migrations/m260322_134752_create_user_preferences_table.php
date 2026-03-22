<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_preferences}}`.
 */
class m260322_134752_create_user_preferences_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%user_preferences}}', [
            'id'         => $this->primaryKey(),
            'user_id'    => $this->integer()->notNull(),
            'theme'      => $this->string(20)->notNull()->defaultValue('light'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex(
            'idx_user_preferences_user_id',
            '{{%user_preferences}}',
            'user_id',
            true
        );

        $this->addForeignKey(
            'fk_user_preferences_user_id',
            '{{%user_preferences}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE',
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_user_preferences_user_user_id', '{{%user_preferences}}');
        $this->dropTable('{{%user_preferences}}');
    }
}

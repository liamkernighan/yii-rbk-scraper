<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%rbk_posts}}`.
 */
class m201020_214120_create_rbk_posts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%rbk_posts}}', [
            'id' => $this->primaryKey(),
            'content' => $this->text()->notNull(),
            'title' => $this->string(100)->notNull(),
            'hash' => $this->string(50)->notNull(),
            'date' => $this->timestamp()->notNull(),
            'category' => $this->string(100)->notNull(),
            'picture' => $this->string(250)->notNull(),
        ]);

        $this->createIndex(
            'idx_rbk_posts_date',
            'rbk_posts',
            'date'
        );

        $this->createIndex(
            'idx_rbk_posts_hash',
            'rbk_posts',
            'hash'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%rbk_posts}}');
    }
}

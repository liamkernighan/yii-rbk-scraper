<?php

namespace app\models;

use Yii;
use yii\helpers\StringHelper;

/**
 * This is the model class for table "rbk_posts".
 *
 * @property int $id
 * @property string $content
 * @property string $title
 * @property string $hash
 * @property string $date
 * @property string $category
 * @property string $picture
 */
class RbkPosts extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rbk_posts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content', 'title', 'hash', 'date', 'category', 'picture'], 'required'],
            [['content'], 'string'],
            [['date'], 'safe'],
            [['title', 'category'], 'string', 'max' => 100],
            [['hash'], 'string', 'max' => 50],
            [['picture'], 'string', 'max' => 250],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'content' => 'Content',
            'title' => 'Title',
            'hash' => 'Hash',
            'date' => 'Date',
            'category' => 'Category',
            'picture' => 'Picture',
        ];
    }

    public function getPreview()
    {
        return StringHelper::truncate(strip_tags($this->content), 200);
    }
}

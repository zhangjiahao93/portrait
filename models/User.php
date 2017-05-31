<?php
namespace zjh\portrait\models;
use Yii;

//只用来修改头像
class User extends\yii\db\ActiveRecord
{    
    public static function tableName()
    {
        $config=Yii::$app->params['zjh_portrait_config'];
        return '{{%'.$config['userModelTableName'].'}}';
    }


}  
<?php
namespace zjh\portrait;
use yii\base\Action;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use Yii;

use zjh\portrait\models\ImageUploadForm;
use zjh\portrait\models\CutPic;
use zjh\portrait\models\User;
Yii::setAlias('@zjh', dirname(__DIR__));
//通用动作测试
class PortraitAction extends Action
{
    public $config=[];
    
    public function init()
    {
        //close csrf
        Yii::$app->request->enableCsrfValidation = false;
        //默认设置
        $_config = require(__DIR__ . '/config.php');

        //添加图片默认root路径；
        $_config['imageRoot'] = Yii::getAlias('@webroot');

        //load config file
        $this->config = ArrayHelper::merge($_config, $this->config);
        Yii::$app->params['zjh_portrait_config'] = $this->config;
        parent::init();
    }
    
    public function run()
    {
        //运行
        $command=Yii::$app->request->get("command");
        if(!empty($command)){
            switch ($command) {
                case "up":
                    $res= self::UpPortrait();
                    break;
                case "save":
                    $res= self::ChangePortrait();
                    break;
                default:
                    $res=[];
                    break;
            }
            return $res;
        }
        
        return $this->controller->render('@zjh/portrait/view/view',[]);
    }

    //头像上传 返回原图名
    public static function UpPortrait(){
        $model = new ImageUploadForm;
        if (Yii::$app->request->isPost) {
            $model->image = \yii\web\UploadedFile::getInstance($model, 'image');
            if($model->public_upload_original()){//只上传原图
                return Json::encode(["status"=>"0","name"=>$model->imageName]);
            }else{
                return Json::encode(['status'=>"1","error"=>current($model->getFirstErrors())]);
            }
        }
    }
    
    //头像上传 返回原图名
    public static function ChangePortrait(){
        $c = CutPic::Cut();
        $model=User::findOne(Yii::$app->user->id);
        $keyword=Yii::$app->params['zjh_portrait_config']['portraitKeyword'];
        $model->$keyword= rtrim(Yii::$app->params['zjh_portrait_config']['publicHeadpicPath'],'/').'/'.$c->picName;
        $model->save(false);
        return Json::encode(['status'=>'0']);
    }
    
}

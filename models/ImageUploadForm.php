<?php
namespace zjh\portrait\models;

use Yii;
use yii\base\Model;
//use yii\web\UploadedFile;

class ImageUploadForm extends Model{
    public $config;//通用配置
    
    public $image;

    public $imageName;//返回的文件名

    public function rules()
    {
        return [
            [['image'], 'file', 'skipOnEmpty' => false, 'extensions' => ['jpg', 'jpeg', 'gif', 'bmp', 'png']],
            [['image'],'validateSize']
         //   [['files'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg', 'maxFiles' => 4],
        ];
    }
    public function init() {
        $this->config=Yii::$app->params['zjh_portrait_config'];
        parent::init();
    }

    public function validateSize($attribute, $params){
        if($this->image->size> $this->config['maxsize'] ){
            $this->addError($attribute,"上传图片大小：".$this->image->size.'超出范围！');
        }
    }
    
    
    //返回新文件名
    public function setImageName(){
        //获取文件MD5
        $md5 = md5_file($this->image->tempName);
        $this->imageName= date("Y/m/d/").$md5.".".$this->image->extension;
    }
    
    //返回设置的通用路径
    public function getPublicPath(){
        return $this->config['imageRoot'].'/'.trim($this->config['publicPath'],'/').'/';
    }
    
    //原图上传保存方法
    public function public_upload_original()
    {
        if ($this->validate()) {
            try{
                $this->setImageName();
                $img_org=$this->getPublicPath().$this->imageName;
                self::check_dir($img_org);
                $this->image->saveAs($img_org);
                return true;
            } catch (\Exception $exc) {
                $this->addError("image", "出错了！".$exc->getMessage());
            } 
        }
        return false;
    }
   
    
    //检查是否存在目录不存在就创建
    public static function check_dir($path) {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                throw new \Exception("图片保存目录 $dir 无法创建！");
            }
        }
        return true;
    }
    
    



}
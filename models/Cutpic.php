<?php
namespace zjh\portrait\models;
use zjh\portrait\models\ImageCompression;
use Yii;
//需要依赖 ImageCompression
class CutPic extends \yii\base\Object{
    //通用配置声明
    public $config;
    
    //第一次手动剪裁配置
    public $FConfig;
    //之后的2次缩放配置(默认缩放为120*120)
    public $CConfig;
    
    public $picName;
    public static function Cut() {
        $arr=[
            "FConfig"=>[
                "baseImg"=>Yii::$app->request->post('url'),
                "width"=>Yii::$app->request->post("width"),
                "height"=>Yii::$app->request->post("height"),
                "offset_x"=>Yii::$app->request->post("left"),
                "offset_y"=>Yii::$app->request->post("top"),
            ],
            "config"=>Yii::$app->params['zjh_portrait_config'],
            'CConfig'=>Yii::$app->params['zjh_portrait_config']['CConfig'],
	];
        return new self($arr);
    }
    
    //返回设置的储存头像的路径
    public function getPublicHeadPath(){
        return $this->config['imageRoot'].'/'.trim($this->config['publicHeadpicPath'],'/').'/';
    }
    //返回设置的储存文件的路径
    public function getPublicPath(){
        return $this->config['imageRoot'].'/'.trim($this->config['publicPath'],'/').'/';
    }
    //返回设置的储存文件的路径
    public function getPublicTmpPath(){
        return $this->config['imageRoot'].'/'.trim($this->config['publicTmpPath'],'/').'/';
    }
    
    //获取原始图片名
    public function getImageName(){
       //var_dump($this->FConfig["baseImg"]);die();
       //表示匹配成功了
       if(strpos($this->FConfig["baseImg"],$this->config['publicPath']) === 0){
           return substr($this->FConfig["baseImg"], strlen($this->config['publicPath']));
       }else{
           throw new \Exception("传入的原始图片地址是无效的！");
       }
    }

    
    //构造运行
    public function init() {
        $this->verify();
        $presentName=$this->getImageName();
        //初始的素材所在地址
        $this->FConfig["baseImg"] = $this->config['imageRoot'].'/'. $this->FConfig["baseImg"];
        //初剪的头像 存放地址（临时存放）
        $this->FConfig["targetImg"] = $this->getPublicTmpPath(). $presentName;
        //最后生成图片 （初剪的缩放） 所以缩略图原图地址为初剪地址的存放地址
        $this->CConfig["baseImg"] = $this->FConfig["targetImg"];
        //生成图片名
        $this->picName= date("Y/m/d/His") . rand(100000, 999999) . "." . self::getExt($this->CConfig["baseImg"]);
        //最后生成的头像地址
        $this->CConfig["targetImg"] = $this->getPublicHeadPath(). $this->picName;
        //运行
        $this->run();
    }

    public function run() {
        //设置默认初剪的属性
        ImageCompression::Manual($this->FConfig);
        //设置缩放或者放大~
        ImageCompression::SpAutoReduce($this->CConfig);
    }

    //验证模块
    public function verify() {
        //验证IC参数
        // if(!$this->IC instanceof ImageCompression){
        // 	throw new \Exception(get_class($this->IC)."不是ImageCompression 的实例");
        // }
        //验证FConfig参数是否存在
        static::is_set(static::F_Attribute(), $this->FConfig);
        //验证CConfig参数是否存在
        static::is_set(static::C_Attribute(), $this->CConfig);
    }

    //验证参数是否存在
    public static function is_set($varr, $conf) {
        foreach ($varr as $v) {
            if (!array_key_exists($v, $conf)) {
                throw new \Exception("缺少参数:" . $v);
            }
        }
    }

    /**
     * 初剪参数 
     * baseImg  原图地址
     * width    原图宽
     * height   原图高
     * offset_x 剪裁的x位移
     */
    //必填参数
    public static function F_Attribute() {
        return [
            "baseImg",
            "width",
            "height",
            //"type",
            "offset_x",
            "offset_y"
        ];
    }
    /**
     * 最终生成缩略图 
     * width    缩略图宽
     * height   缩略图高
     */
    public static function C_Attribute() {
        return [
            //"baseImg",
            "width",
            "height",
            //"type",
        ];
    }

    //获取后缀
    public static function getExt($path) {
        $end=explode('.', $path); 
        return strtolower(end($end));
    }
    

//    public static function get_document_root() {
//        return $_SERVER["CONTEXT_DOCUMENT_ROOT"];
//    }

    //自动属性加载
//    public static function configure($object, $properties) {
//        foreach ($properties as $name => $value) {
//            $object->$name = $value;
//        }
//        return $object;
//    }

}

<?php
namespace zjh\portrait;
use yii\web\AssetBundle;

class PortraitAsset extends AssetBundle
{
     public $css = [
  //      "bootstrap-3.3.7/css/bootstrap.min.css",
        'jq.thumb/css/style.css',
        'Jcrop-0.9.12/css/jquery.Jcrop.min.css',
        'portrait.css'

    ];
    public $js = [
  //      'bootstrap-3.3.7/js/bootstrap.min.js',
        'Jcrop-0.9.12/js/jquery.Jcrop.min.js',
        'jq.thumb/js/jquery.thumb.js',
        
    ];
    
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
//    public $jsOptions = [  
//        'position' => \yii\web\View::POS_HEAD,   // 这是设置所有js放置的位置  
//    ];  
   
    public function init()
    {
        $this->sourcePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
    }
}
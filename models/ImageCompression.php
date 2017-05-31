<?php
namespace zjh\portrait\models;

//需要依赖arrayheper
//require_once("ArrayHelper.php");
/*
  [
  "baseImg"=>"原图地址",
  "targetImg"=>"目标储存地址",
  "width"=>"目标图宽度",
  "height"=>"目标图高度",
  "type"=>0,
  "offset_x"=>"原图偏离",
  "offset_y"=>"原图偏离y"]
 */

/**
 *
 * 图像处理类
 * @author FC_LAMP
 * @internal功能包含：水印,缩略图
 */
class ImageCompression extends \yii\base\Object{
    public $config;//通用配置
    //图片格式
    public $exts = ['jpg', 'jpeg', 'gif', 'bmp', 'png'];
    //原图地址
    public $baseImg;
    //目标图片地址
    public $targetImg;
    //需要处理成的长宽
    public $width;
    public $height;
    //图片处理方式
    public $type = '0'; //0缩放+剪裁处理,1缩放不剪裁,2相对原图剪裁
    //当前图片的后缀----------
    public $ext;
    //获取到的对应图片格式处理函数
    public $org_funcs;
    //原图宽
    public $o_w;
    //原图高
    public $o_h;
    //gd创建的原图对象
    public $obj;
    //与原图偏离x
    public $offset_x = 0;
    //与原图偏离y
    public $offset_y = 0;
    //生成图片的高
    public $nowHeight;
    //生成图片的高
    public $nowWidth;
    //生成图片的比例 -按比例缩放才有
    public $ratio;

//-- 快捷方法-----------------------------------
    //自动剪裁缩放
    public static function AutoCut($config) {
        $config["type"] = "0";
        return new self($config);
    }

    //自动缩放
    public static function AutoReduce($config) {
        $config["type"] = "1";
        return new self($config);
    }

    //手动缩放/拉伸/剪裁 图片
    public static function Manual($config) {
        $config["type"] = "2";
        return new self($config);
    }

    //自动缩放 可以将小图放大
    public static function SpAutoReduce($config) {
        $config["type"] = "3";
        return new self($config);
    }

//-----------------------------------------------    

    public function __construct($config = []) {
        if (!function_exists('gd_info')) {
            throw new \Exception('加载GD库失败！');
        }
        //自动添加属性
        static::configure($this, $config);
        $this->run();
    }

    public function run() {
        //验证参数
        $this->verify();
        //采取缩放剪裁
        if ($this->type == "0") {
            return $this->thumb_img();
        }
        if ($this->type == "1") {
            return $this->resize_image();
        }
        if ($this->type == "2") {
            return $this->cut_image();
        }
        if ($this->type == "3") {
            return $this->resize_image_sp();
        }
    }

    //验证参数
    public function verify() {
        //验证长宽
        if (empty($this->width) && empty($this->height)) {
            throw new \Exception('原图长度与宽度不能小于0');
        }
        //获取并验证图片后缀
        $this->ext = $this->is_img($this->baseImg);

        //如果有保存路径，则确保路径正确
        $this->check_dir();

        //获取出相应图片的方法
        $this->org_funcs = $this->get_img_funcs($this->ext);

        //获取原图对象
        $this->obj = $this->org_funcs ['create_func']($this->baseImg);
        //获取长宽
        $this->o_w = imagesx($this->obj);
        $this->o_h = imagesy($this->obj);

        if ($this->type == "0") {
            //缩放剪裁必须 长宽都要小于原图
            if ($this->o_w < $this->width || $this->o_h < $this->height) {
                throw new \Exception('上传图片大小必须大于 ' . $this->width . '*' . $this->height);
            }
        }
        if ($this->type == "1") {
            //等比缩放最少长或宽要有一项要大于原图
            if ($this->o_w < $this->width && $this->o_h < $this->height) {
                throw new \Exception('上传图片要么宽大于' . $this->width . "(" . $this->o_w . ")" . '要么长大于' . $this->height . "(" . $this->o_h . ")");
            }
        }
    }

    //自动加载
    public static function configure($object, $properties) {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }
        return $object;
    }

    /**
     *
     * 裁剪压缩
     * @param $src_img  原始图片路径
     * @param $this->targetImg 生成后的图片路径
     * @param $option  	参数选项，包括： $maxwidth  宽  $maxheight 高
     * array('width'=>xx,'height'=>xxx)
     * @internal
     * 我们一般的压缩图片方法，在图片过长或过宽时生成的图片
     * 都会被“压扁”，针对这个应采用先裁剪后按比例压缩的方法
     */
    public function thumb_img() {

        //调整原始图像(保持图片原形状裁剪图像)
        $dst_scale = $this->height / $this->width; //目标图像长宽比
        $src_scale = $this->o_h / $this->o_w; // 原图长宽比

        if ($src_scale >= $dst_scale) { // 过高
            $w = intval($this->o_w);
            $h = intval($dst_scale * $w);

            $x = 0;
            $y = ($this->o_h - $h) / 3;
        } else { // 过宽
            $h = intval($this->o_h);
            $w = intval($h / $dst_scale);

            $x = ($this->o_w - $w) / 2;
            $y = 0;
        }
        // 剪裁
        $croped = imagecreatetruecolor($w, $h);

        //imagecopy ( resource $dst_im , resource $src_im , int $dst_x , int $dst_y , int $src_x , int $src_y , int $src_w , int $src_h )
        //将 src_im 图像中坐标从 src_x，src_y 开始
        //宽度为 src_w，高度为 src_h 的一部分拷贝到 dst_im 图像中坐标为 dst_x 和 dst_y 的位置上。
        //主图           //主x,主y,主宽,主高
        imagecopy($croped, $this->obj, 0, 0, $x, $y, $this->o_w, $this->o_h);
        // 缩放
        $scale = $this->width / $w;
        $target = imagecreatetruecolor($this->width, $this->height);
        $final_w = intval($w * $scale);
        $final_h = intval($h * $scale);

        //x,y,mx,my,dw,dh,mw,mh
        imagecopyresampled($target, $croped, 0, 0, 0, 0, $final_w, $final_h, $w, $h);
        imagedestroy($croped);

        //保存
        $this->saveImage($target);

        return true;
    }

    /**
     * 自定义剪裁图片
     */
    public function cut_image() {

        $target = imagecreatetruecolor($this->width, $this->height);

        imagecopyresampled($target, $this->obj, 0, 0, $this->offset_x, $this->offset_y, $this->width, $this->height, $this->width, $this->height);

        //保存
        $this->saveImage($target);

        return true;
    }

    /*
     * 特殊放大缩小
     */

    public function resize_image_sp() {
        $target = imagecreatetruecolor($this->width, $this->height);

        //如果原图比新生成的图要大则采用缩放
        if ($this->height > $this->o_h) {
            //目标分辨率比原图大的情况下
            //原图最终长宽都等于目标长宽---表现为放大图片                                                                                
            imagecopyresampled($target, $this->obj, 0, 0, 0, 0, $this->width, $this->height, $this->o_w, $this->o_h);
            //保存
            $this->saveImage($target);
        } else {
            //否则采用自动缩放
            $this->resize_image();
        }

        return true;
    }

    /**
     *
     * 等比例缩放图像
     * @param $src_img 原图片
     * @param $this->targetImg 需要保存的地方
     * @param $option 参数设置 array('width'=>xx,'height'=>xxx)
     *
     */
    public function resize_image() {
        $resizewidth_tag = $resizeheight_tag = false;
        //如果生成图片的宽 比原图宽要短
        if ($this->width && $this->o_w >= $this->width) {
            $widthratio = $this->width / $this->o_w;
            $resizewidth_tag = true;
        }
        //如果生成图片的高 比原图高要短
        if ($this->height && $this->o_h >= $this->height) {
            $heightratio = $this->height / $this->o_h;
            $resizeheight_tag = true;
        }
        //上述两个都成立
        if ($resizewidth_tag && $resizeheight_tag) {
            //高的比例大于宽的比例
            if ($widthratio < $heightratio) {
                $ratio = $widthratio;
            } else {
                $ratio = $heightratio;
            }
        }
        //生成的高比原图高要高
        if ($resizewidth_tag && !$resizeheight_tag) {
            $ratio = $widthratio; //比例等于宽度的比例
        }
        //生成的宽比原图宽要宽
        if ($resizeheight_tag && !$resizewidth_tag) {
            $ratio = $heightratio;
        }
        if(empty($ratio)){
           throw new \Exception("原图不能比缩放的图还小！");
        }
        
        
        //生成新的宽高
        $newwidth = $this->o_w * $ratio;
        $newheight = $this->o_h * $ratio;
        $this->nowHeight=$newheight;
        $this->nowWidth=$newwidth;
        $this->ratio=$ratio;
        
        if (function_exists("imagecopyresampled")) {
            $target = imagecreatetruecolor($newwidth, $newheight);
            imagecopyresampled($target, $this->obj, 0, 0, 0, 0, $newwidth, $newheight, $this->o_w, $this->o_h);
        } else {
            $target = imagecreate($newwidth, $newheight);
            imagecopyresized($target, $this->obj, 0, 0, 0, 0, $newwidth, $newheight, $this->o_w, $this->o_h);
        }
        //保存
        $this->saveImage($target);
    }

    //输出(保存)图片
    public function saveImage($target) {
        if (!empty($this->targetImg)) {
            $this->org_funcs ['save_func']($target, $this->targetImg);
        } else {
            header($this->org_funcs ['header']);
            $this->org_funcs ['save_func']($target);
        }
        //销毁画布
        imagedestroy($target);
        return true;
    }

    /**
     * 检查图片后缀是否正确
     */
    private function is_img($img_path) {
        if (!file_exists($img_path)) {
            throw new \Exception("加载图片 $img_path 失败！");
        }
        $ext = explode('.', $img_path);
        $ext = strtolower(end($ext));
        if (!in_array($ext, $this->exts)) {
            throw new \Exception("图片 $img_path 格式不正确！");
        }
        return $ext;
    }

    /**
     *
     * 返回正确的图片函数
     * @param unknown_type $ext
     */
    private function get_img_funcs($ext) {
        //选择
        switch ($ext) {
            case 'jpg' :
                $header = 'Content-Type:image/jpeg';
                $createfunc = 'imagecreatefromjpeg';
                $savefunc = 'imagejpeg';
                break;
            case 'jpeg' :
                $header = 'Content-Type:image/jpeg';
                $createfunc = 'imagecreatefromjpeg';
                $savefunc = 'imagejpeg';
                break;
            case 'gif' :
                $header = 'Content-Type:image/gif';
                $createfunc = 'imagecreatefromgif';
                $savefunc = 'imagegif';
                break;
            case 'bmp' :
                $header = 'Content-Type:image/bmp';
                $createfunc = 'imagecreatefrombmp';
                $savefunc = 'imagebmp';
                break;
            default :
                $header = 'Content-Type:image/png';
                $createfunc = 'imagecreatefrompng';
                $savefunc = 'imagepng';
        }
        return ['save_func' => $savefunc, 'create_func' => $createfunc, 'header' => $header];
    }

    /**
     *
     * 检查并试着创建目录
     * @param $src
     */
    private function check_dir() {

        if (!empty($this->targetImg)) {
            $dir = dirname($this->targetImg);
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0777, true)) {
                    throw new \Exception("图片保存目录 $dir 无法创建！");
                }
            }
        }
        return true;
    }

}

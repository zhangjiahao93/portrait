#使用方法
#在控制器中配置一下
#xxxController:
public function actions(){
	return [
		'headpic'=>[
			'class'=>'zjh\portrait\PortraitAction',
			'config' => [ 
				'imageRoot' =>Yii::getAlias('@webroot'),//总目录地址D://webpath/
				"publicPath"=>"/uploads/public/original/",//原图保存路径
				"publicHeadpicPath"=>"/uploads/public/headpic/",//默认头像保存路径
				"publicTmpPath"=>"/uploads/public/headpicTmp/",//默认头像保存路径
				'CConfig' => [
					"width" => 120,
					"height" => 120
				],//最终头像大小设置
				'maxsize'=>1024*1024*2,//2M上传图片最大尺寸
				'userModelTableName'=>'user_backend',//需要修改头像的用户模型
				'portraitKeyword'=>'thumb',//用户表要修改的关键词
			]
		]
	]
}
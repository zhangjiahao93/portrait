#ʹ�÷���
#�ڿ�����������һ��
#xxxController:
public function actions(){
	return [
		'headpic'=>[
			'class'=>'zjh\portrait\PortraitAction',
			'config' => [ 
				'imageRoot' =>Yii::getAlias('@webroot'),//��Ŀ¼��ַD://webpath/
				"publicPath"=>"/uploads/public/original/",//ԭͼ����·��
				"publicHeadpicPath"=>"/uploads/public/headpic/",//Ĭ��ͷ�񱣴�·��
				"publicTmpPath"=>"/uploads/public/headpicTmp/",//Ĭ��ͷ�񱣴�·��
				'CConfig' => [
					"width" => 120,
					"height" => 120
				],//����ͷ���С����
				'maxsize'=>1024*1024*2,//2M�ϴ�ͼƬ���ߴ�
				'userModelTableName'=>'user_backend',//��Ҫ�޸�ͷ����û�ģ��
				'portraitKeyword'=>'thumb',//�û���Ҫ�޸ĵĹؼ���
			]
		]
	]
}
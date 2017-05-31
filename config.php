<?php

return[
    //添加图片默认root路径；
    "publicPath"=>"/uploads/public/original",//原图保存路径
    "publicHeadpicPath"=>"/uploads/public/headpic",//默认头像保存路径
    "publicTmpPath"=>"/uploads/public/headpicTmp",//默认头像保存路径
    'CConfig' => [
        "width" => 120,
        "height" => 120
    ],//最终头像大小设置
    'maxsize'=>1024*1024*2,//2M
    'userModelTableName'=>'user_backend',//需要修改头像的用户模型
    'portraitKeyword'=>'thumb',//用户表要修改的关键词
    
];
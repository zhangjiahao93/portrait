<?php
$this->context->layout = false; //不使用布局
use yii\helpers\Url;
use zjh\portrait\PortraitAsset;
use yii\helpers\Html;
PortraitAsset::register($this);
$present=Yii::$app->controller->id.'/'.Yii::$app->controller->action->id;
?>
<?php $this->beginBlock('js') ?>  
    $(function () {
	$.Thumb.display({
		main:"#thumb_main",
		thumb:".smpic",
		input:"#upinput",
                upImageName:"ImageUploadForm[image]",
		uploadServer:"<?=Url::to([$present,"command"=>"up"])?>",
		processServer:"<?=Url::to([$present,"command"=>"save"])?>"
	});
        
        $(".btn_sub").click(function () {
            var data = $('form').serialize();
            var index;
            $.ajax({
                type: "POST",
                url: document.location.href,
                data: data,
                //processData: false,//不自动解析
                dataType: "json",
                beforeSend: function () {
                    index = layer.load(1, {time: 10 * 1000});
                },
                success: function (msg) {
                    layer.close(index);
                    if (msg.status === '0') {
                        layer.open({
                            content: '处理成功！',
                            icon: '1',
                            yes: function (index, layero) {
                                layer.load(1);
                                location.reload();//刷新父
                                //parent.layer.close(frameindex);
                                layer.close(index); //如果设定了yes回调，需进行手工关闭
                                //document.location.href=msg;
                            }
                        });
                    } else {
                        layer.open({
                            content: '处理错误！' + msg.error,
                            icon: '2',
                            yes: function (index, layero) {
                                layer.load(1);
                                location.reload();//刷新父
                                layer.close(index); //如果设定了yes回调，需进行手工关闭
                            }
                        });
                    }
                },
                error: function (msg) {
                    layer.close(index);
                    layer.alert('服务器错误，请稍后重试！', {icon: 2});
                }
            });
            return false;
        });
    })
<?php $this->endBlock() ?>  
<?php $this->registerJs($this->blocks['js'], \yii\web\View::POS_END); ?>  
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<?php $this->beginBody() ?>
<body class="gray-bg">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-content">
                        <ul class="nav nav-tabs" >
                            <li class="active" ><a href="javascript:;">本地上传</a></li>
                        </ul>
                        <div class="m-t m-b">
                            <input type="file" name="file" id="upinput" />
                            <div class="image_dispaly">
                                <div id="thumb_main" ><p>上传图片预览</p></div>
                                <div class="smpic" style="width: 180px;height: 180px"><p>头像预览</p></div>
                                <div class="smpic" style="width: 90px;height: 90px"><p>头像预览</p></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body> 
<?php $this->endBody() ?>
</html>
<?php $this->endPage() ?>





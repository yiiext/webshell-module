<?php
use yii\helpers\Html;
?>
<?php $this->beginPage() ?>
<!doctype html>
<html>
<head>
    <meta charset="<?php echo Yii::$app->charset?>">
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<?php echo $content?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
<?php
$this->pageTitle=Yii::app()->name . ' - Error';
?>
<h2>Error <?php echo $code; ?></h2>
<?php echo CHtml::encode($message) ?>
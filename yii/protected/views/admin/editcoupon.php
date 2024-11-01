<?php
if($iscreate){
    $action='createcoupon';
} else {
    $action='editcoupon&id='.$model->id;
}
$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id' => 'user-form',
    'type' => 'vertical',
    'action' => admin_url().'admin.php?page=wpmm_orders&action='.$action,
    'enableAjaxValidation' => true,
    'clientOptions' => array(
      'validateOnSubmit' => true,
      'validateOnChange' => false,
    ),
    'htmlOptions' => array('data-ajax' => MM_AJAX_URL),
        ))
?>
<legend>
    <div style="float:left"><?php if (!$iscreate) { ?> Update Coupon - <?php echo $model->name;
    } else { ?>Create Coupon<?php } ?></div>
    <?php
                        Yii::app()->clientScript->registerScript(
                            'apply-save actions',
                            "jQuery('#apply_submit').click(function() {
                               jQuery('#form_apply_ckeck').attr('value','true');
                            });
                            jQuery('#save_submit').click(function() {
                               jQuery('#form_apply_ckeck').attr('value','false');
                            });");
                        ?>
    <div align="right" style="margin-right: 10px">
        <?php  $this->widget('bootstrap.widgets.TbButton', array('buttonType'=>'submit','icon'=>'ok white','type'=>'primary' ,'label'=>'Save Changes', 'htmlOptions'=>array('name'=>'wpmm_menus','id'=>'save_submit',))); ?>
        <?php  $this->widget('bootstrap.widgets.TbButton', array('buttonType'=>'submit','icon'=>'ok white','type'=>'success' ,'label'=>'Apply Changes', 'htmlOptions'=>array('name'=>'wpmm_menus-apply','id'=>'apply_submit'))); ?>
    <?php $this->widget('bootstrap.widgets.TbButton',array('url'=>'admin.php?page=wpmm_orders&action=coupons','label' => 'Close','type' => 'danger','icon'=>'remove white')); ?>
    </div>
</legend>
        <?php if (!$iscreate) {
                 echo Chtml::hiddenField('ajax_validation', $model->id );
            } else {
                echo Chtml::hiddenField('ajax_validation', '0' );
                } ?>
        <?php echo Chtml::hiddenField('action', 'admin'); ?>
        <?php echo Chtml::hiddenField('model_name', 'MMCoupons'); ?>
        <?php echo Chtml::hiddenField('apply', 'false',array('id'=>'form_apply_ckeck')); ?>
<div class="mm-container">
    <?php
    $this->beginWidget('bootstrap.widgets.TbBox', array(
        'title' => 'General',
        'headerIcon' => 'icon-pencil',
        'htmlOptions' => array('class' => 'bootstrap-widget-table')
    ))
    ?>
    <div class="inrfrm">
        <table border="0" cellpadding="5" >
            <tr>
                <td valign="top"><?php echo $form->textFieldRow($model, 'name'); ?></td>
                <td valign="top"><?php echo $form->textFieldRow($model, 'code'); ?><?php $this->widget('bootstrap.widgets.TbButton', array('url' => '#', 'label' => 'Gen. Code', 'icon' => 'repeat','id'=>'wpmm-admin-gen-code','htmlOptions'=>array('style'=>'margin-left: 5px; margin-bottom: 10px;'))); ?></td>
                <td><td>
            </tr>
            <tr>
                <?php if (!$iscreate){
                    $cfromtemp=$model->cfrom;
                    $model->cfrom=date('m/d/Y', strtotime($model->cfrom)) .' - '. date('m/d/Y',strtotime($model->cto));
                    ?>
                <td valign="top"> <?php echo $form->dateRangeRow($model, 'cfrom', array('prepend'=>'<i class="icon-calendar"></i>', 'options' => array('startDate'=>date('m/d/Y', strtotime($cfromtemp)),'endDate'=>date('m/d/Y',strtotime($model->cto))))); ?></td>
                <?php } else {?>
                <td valign="top"> <?php echo $form->dateRangeRow($model, 'cfrom', array('prepend'=>'<i class="icon-calendar"></i>')); ?></td>
                <?php } ?>
            </tr>
            <tr>
               <td valign="top"><?php echo $form->textFieldRow($model, 'amount'); ?></td>
               <td valign="top">
<?php echo $form->radioButtonListInlineRow($model, 'type', array('1' => 'Percentage, %', '0' => 'Specific amount, $',)) ?>
                </td>
            <tr>
        </table>
        <table border="0" cellpadding="5"><tr>
                <td valign="top">
<?php echo $form->radioButtonListInlineRow($model, 'active', array('1' => 'YES', '0' => 'NO',)) ?>
                </td>
            </tr>
        </table>
    </div>
<?php $this->endWidget() ?>
<?php $this->endWidget() ?>
</div>
<script>
jQuery('#wpmm-admin-gen-code').on('click', function(){
   var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for( var i=0; i < 7; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
        jQuery('#MMCoupons_code').val(text);
        return false;
    });
        </script>
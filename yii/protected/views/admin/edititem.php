<?php
if($isnew){
    $action='createitem';
} else {
    $action='updateitem&id='.$model->id;
}
$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id' => 'items-form',
    'type' => 'vertical',
    'action' => admin_url().'admin.php?page=wpmm_items&action='.$action,
    'enableAjaxValidation' => true,
    'clientOptions' => array(
        'validateOnSubmit' => true,
        'validateOnChange' => false,
    ),
    'htmlOptions' => array('enctype' => 'multipart/form-data', 'data-ajax' => MM_AJAX_URL),
        ))
?>

<legend>
    <div style="float:left"><?php if (!$isnew) { ?> Update Item - <?php
        echo $model->name;
    } else {
    ?>Create Item<?php } ?></div>
    <?php
    Yii::app()->clientScript->registerScript(
            'apply/save actions', "jQuery('#apply_submit').click(function() {
                               jQuery('#form_apply_ckeck').attr('value','true');
                            });
                            jQuery('#save_submit').click(function() {
                               jQuery('#form_apply_ckeck').attr('value','false');
                            });");
    ?>
    <div align="right" style="margin-right: 10px">
        <?php $this->widget('bootstrap.widgets.TbButton', array('buttonType' => 'submit', 'icon' => 'ok white', 'type' => 'primary', 'label' => 'Save Changes', 'htmlOptions' => array('name' => 'wpmm-items', 'id' => 'save_submit',))); ?>
        <?php $this->widget('bootstrap.widgets.TbButton', array('buttonType' => 'submit', 'icon' => 'ok white', 'type' => 'success', 'label' => 'Apply Changes', 'htmlOptions' => array('name' => 'wpmm-items-apply', 'id' => 'apply_submit'))); ?>
        <?php $this->widget('bootstrap.widgets.TbButton', array('url' => 'admin.php?page=wpmm_items', 'label' => 'Close', 'type' => 'danger', 'icon' => 'remove white')); ?>
    </div>
</legend>
<div class="mm-container">
    <?php
    if (!$isnew) {
        echo Chtml::hiddenField('ajax_validation', $model->id);
    } else {
        echo Chtml::hiddenField('ajax_validation', '0');
    }
    ?>
    <?php echo Chtml::hiddenField('action', 'admin'); ?>
    <?php echo Chtml::hiddenField('model_name', 'MMItem'); ?>
    <?php echo Chtml::hiddenField('apply', 'false', array('id' => 'form_apply_ckeck')); ?>
    <?php
    $this->beginWidget('bootstrap.widgets.TbBox', array(
        'title' => 'General',
        'headerIcon' => 'icon-pencil',
        'htmlOptions' => array('class' => 'bootstrap-widget-table')
    ))
    ?>
    <div class="inrfrm">
        <table border="0" cellpadding="5">
            <tr>
                <td valign="top" align="left">
                    <?php echo $form->textFieldRow($model, 'name') ?>
                </td>
                <td valign="top" align="left"><?php echo $form->textFieldRow($model, 'price'); ?></td>
                <td valign="top" align="left">
                <?php echo $form->fileFieldRow($upload, 'file') ?>
                </td>
                <td valign="top" align="left">
                <?php echo $form->radioButtonListInlineRow($model, 'published', array('1' => 'YES', '0' => 'NO',)); ?>
                </td>
                    <?php
                    if (!empty($model->image)) {
                        ?><td valign="middle">
                        <?php echo CHtml::activeHiddenField($model, 'image') ?>
                        <?php
                        $this->widget('bootstrap.widgets.TbButton', array(
                            'label' => 'View Image',
                            'type' => 'success',
                            'icon' => 'picture white',
                            'id' => 'imadialog',
                            'htmlOptions' => array(
                                'data-toggle' => 'modal',
                                'data-target' => '#myModal',
                                'style' => 'margin-top:10px;',
                            ),
                        ));
                        ?>
    <?php $this->beginWidget('bootstrap.widgets.TbModal', array('id' => 'myModal')) ?>

                        <div class="modal-header">
                            <a class="close" data-dismiss="modal">×</a>
                            <h4>Menu image</h4>
                        </div>

                        <div class="modal-body">
                            <div align="center">
                                <img style="height: 400px;" src="<?php echo MM_UPLOADS_URL . "/" . $model->image ?>">
                            </div>
                        </div>

                        <div class="modal-footer">
                            <?php
                            $this->widget('bootstrap.widgets.TbButton', array(
                                'type' => 'primary',
                                'label' => 'OK',
                                'url' => '',
                                'htmlOptions' => array('data-dismiss' => 'modal'),
                            ))
                            ?>
                            <?php
                            $this->widget('bootstrap.widgets.TbButton', array(
                                'label' => 'Delete',
                                'type' => 'danger',
                                'url' => '',
                                'htmlOptions' => array(
                                    'onClick' => "
                                        jQuery.ajax({
                                            'type':'POST',
                                            'data':{'" . Yii::app()->request->csrfTokenName . "':'" . Yii::app()->request->csrfToken . "'},
                                            'success':function( data ) {
                                                jQuery('#MMItem_image').val(''); jQuery('#myModal').modal('hide');jQuery('#imadialog').addClass('disabled');
                                                jQuery('#imadialog').bind('click', false);
                                            },
                                            'url':'" . admin_url() . 'admin.php?page=wpmm_items&action=delpictureitem&id=' . $model->id . "','cache':false});
                                    ",
                                ),
                            ))
                            ?>
                        </div>
    <?php $this->endWidget() ?>
                    </td>
<?php }  ?>
            </tr>
        </table>
        <table border="0" cellpadding="5" width="100%">
            <tr>
                <td valign="top"><?php echo $form->textAreaRow($model, 'description', array('class' => 'span8', 'rows' => 8, 'style' => "width:100%;")) ?></td>
                <td valign="top" width="210">
<?php echo CHtml::label('Assosiate with Categories', 'Item_categories') ?>
                    <div class="checkboxlist">
<?php echo Helpers::activeCheckBoxListMany($model, 'categories', CHtml::listData(MMCategory::model()->findAll(array('order' => 'id')), 'id', 'name'), array('attributeitem' => 'id'))
?>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <?php $this->endWidget() ?>
    <?php
    $this->beginWidget('bootstrap.widgets.TbBox', array(
        'title' => 'Attributes',
        'headerIcon' => 'icon-tasks',
        'htmlOptions' => array('class' => 'bootstrap-widget-table')
    ))
    ?>
    <div style="margin:10px;" id="addgroup_id">
        <?php echo Chtml::hiddenField('group_items', 'group') ?>
        <?php echo Chtml::hiddenField('gid', '0') ?>
        <?php echo Chtml::hiddenField('iid', $model->id) ?>
        <?php
        $this->widget('bootstrap.widgets.TbButton', array(
            'label' => 'Add Group',
            'type' => 'primary',
            'url' => '',
            'htmlOptions' => array('onClick' => "jQuery.ajax({
                'type':'POST',
                'success':function( data ) {
                    // handle return data
                    jQuery('#groups').append(data);
                  },
                'url':'" . MM_AJAX_URL . "','cache':false,
                'data':jQuery('#addgroup_id').children().serialize()+'&action='+ encodeURIComponent('admin')+'&". Yii::app()->request->csrfTokenName ."='+encodeURIComponent('" .Yii::app()->request->csrfToken. "')
                });"
            ),
        ))
        ?>
    </div>
    <div id="groups">
        <?php
        if (!$isnew) {
            if (!empty($groups)) {
                foreach ($groups as $group) {
                    echo '<div class="grpfrm">';
                    echo "<div id='group{$group->id}'><label>Group Name: </label>";
                    echo CHtml::activeTextField($group, "name[]", array('value' => $group->name)) . '<label> Group type: </label>';
                    echo CHtml::activeDropDownList($group, "type[]", $group->getTypeList(), array('value' => $group->type, 'options' => array("{$group->type}" => array('selected' => true))));
                    echo CHtml::activeHiddenField($group, "id[]", array('value' => $group->id));
                    echo CHtml::activeHiddenField($group, "item_id[]", array('value' => $group->item_id));
                    $this->widget('bootstrap.widgets.TbButton', array(
                        'label' => '',
                        'type' => 'danger',
                        'size' => 'mini',
                        'icon' => 'remove white',
                        'url' => '',
                        'id' => 'group' . $group->id,
                        'htmlOptions' => array('onClick' => "
                        jQuery.ajax({'type':'POST','success':function( data ) {
                                        jQuery('#group{$group->id}').parent().remove();
                                            },'url':'" . admin_url() . 'admin.php?page=wpmm_items&action=delgroup&id=' . $group->id . "','cache':false});
                                       ", 'style' => 'margin:2px; margin-bottom:10px;', 'rel' => 'tooltip', 'title' => 'Remove Group',
                        ),
                    ));
                    echo '</div>';
                    echo '<div id="' . $group->id . '" class="wp-attrib">';
                    if (isset($group->attribs)) {
                        foreach ($group->attribs as $attr) {
                            echo "<div id='attr'><label>Name: </label>";
                            echo CHtml::activeTextField($attr, "name[]", array('value' => $attr->name)) . '<label>Price: </label>';
                            echo CHtml::activeTextField($attr, "price[]", array('style' => 'width:50px;', 'value' => $attr->price)) . ' ';
                            echo CHtml::CheckBox("checked_id_" . $attr->id, ($attr->checked_id > 0) ? true : false, array('uncheckValue' => 0));
                            echo CHtml::activeHiddenField($attr, "group_id[]", array('value' => $attr->group_id));
                            echo CHtml::activeHiddenField($attr, "id[]", array('value' => $attr->id));
                            $this->widget('bootstrap.widgets.TbButton', array(
                                'label' => '',
                                'type' => 'danger',
                                'size' => 'mini',
                                'icon' => 'remove white',
                                'url' => '',
                                'id' => 'attr' . $attr->id,
                                'htmlOptions' => array('onClick' => "
                                       jQuery.ajax({'type':'POST','success':function( data ) {
                                        jQuery('#attr{$attr->id}').parent().remove();
                                            },'url':'" . admin_url() . 'admin.php?page=wpmm_items&action=delattrib&id=' . $attr->id . "','cache':false});
                                       ", 'style' => 'margin:6px;', 'rel' => 'tooltip', 'title' => 'Remove Attribute',
                                ),
                            ));
                            echo '</div>';
                        }
                    }
                    echo '</div>';
                    $uid = uniqid('form');
                    echo "<div id='{$uid}'>";
                    echo Chtml::hiddenField('g_id', $group->id);
                    echo Chtml::hiddenField('attribure_items', 'items');
                    $this->widget('bootstrap.widgets.TbButton', array(
                        'label' => 'Add Item',
                        'url' => '',
                        'htmlOptions' => array('onClick' => "jQuery.ajax({
                            'type':'POST',
                            'data':{'" . Yii::app()->request->csrfTokenName . "':'" . Yii::app()->request->csrfToken . "'},
                            'success':function( data ) {
                                // handle return data
                                jQuery('#{$group->id}').append(data);
                                var actual = jQuery('#{$group->id}').parent().find('select');
                                    if (jQuery(actual).find(':selected').text() == 'Single-choise') {
                                        if (jQuery('#{$group->id}').parent().find('#attr input:checked').length == 0) {
                                            jQuery('#{$group->id}').parent().find('#attr input:checkbox').attr('checked', false);
                                            jQuery('#{$group->id}').parent().find('#attr input:checkbox:first').attr('checked', true);
                                        }
                                    }
                            },
                            'url':'" . MM_AJAX_URL . "','cache':false,
                            'data':jQuery('#{$uid}').children().serialize()+'&action='+encodeURIComponent('admin')+'&". Yii::app()->request->csrfTokenName ."='+encodeURIComponent('" .Yii::app()->request->csrfToken. "')
                        });"
                        ),
                    ));
                    echo '</div>';
                    echo '</div>';
                }
            }
        }
        ?>
    </div>
<?php $this->endWidget() ?>
<?php $this->endWidget() ?>
</div>
<?php
$script = <<<SCRIPT
jQuery(".grpfrm select").change(function(){
    if(jQuery(this).find(':selected').text() == 'Single-choise'){
        jQuery(this).parent().parent().find('#attr input:checkbox').attr('checked', false);
        jQuery(this).parent().parent().find('#attr input:checkbox:first').attr('checked', true);
    } else {
        jQuery(this).parent().parent().find('#attr input:checkbox').attr('checked', false);
    }
    });
jQuery(".grpfrm input:checkbox").click(function(){
    //alert(jQuery(this).parent().parent().parent().find(":selected").text());
var typegroup = jQuery(this).parent().parent().parent().find(":selected").text();
var parent_id= jQuery(this).parent().parent().parent().find(":selected").parent();
    if(typegroup == "Single-choise"){
       jQuery(this).parent().parent().find("input:checkbox").attr('checked', false);
       jQuery(this).attr('checked',true);
    }
});
SCRIPT;
Yii::app()->clientScript->registerScript('checkbox-handle', $script);
?>
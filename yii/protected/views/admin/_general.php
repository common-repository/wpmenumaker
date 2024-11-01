<legend>General</legend>
<div class="mm-container">
    <?php
    $this->beginWidget('bootstrap.widgets.TbBox', array(
        'title' => 'Pricing',
        'headerIcon' => 'icon-flag',
        'htmlOptions' => array('class' => 'bootstrap-widget-table')
    ))
    ?>
    <div style="float: left; padding: 10px;">
        <?php
        echo $form->textFieldRow($model, 'tax_rate', array('prepend' => '<i class="icon-cog"></i>'));
        echo $form->textFieldRow($model, 'delivery_charge', array('prepend' => '<i class="icon-flag"></i>'));
        ?>
    </div>
    <div style="float: left; padding: 10px;">
        <?php
        echo $form->textFieldRow($model, 'discount', array('prepend' => '<i class="icon-gift"></i>'));
        echo $form->textFieldRow($model, 'min_order', array('prepend' => '<i class="icon-thumbs-down"></i>'));
        ?>
    </div>
    <?php $this->endWidget() ?>
    <table border="0" width="100%">
        <tr>
            <td valign="top" width="250">
    <?php
    $this->beginWidget('bootstrap.widgets.TbBox', array(
        'title' => 'Authorize.net payments',
        'headerIcon' => 'icon-signal',
        'htmlOptions' => array('class' => 'bootstrap-widget-table')
    ))
    ?>
    <div style="float: left; padding: 10px;">
        <div style="float: left; padding: 10px;">
        <?php
        echo $form->textFieldRow($model, 'api_login_id', array('prepend' => '<i class="icon-asterisk"></i>'));
        echo $form->textFieldRow($model, 'transaction_key', array('prepend' => '<i class="icon-wrench"></i>'));
        ?>
        <?php  echo $form->toggleButtonRow($model, 'enable_payments');  ?>
            </div>
    </div>
    <?php $this->endWidget() ?>
            </td>
            <td valign="top">
    <?php
    $this->beginWidget('bootstrap.widgets.TbBox', array(
        'title' => 'Paypal payments',
        'headerIcon' => 'icon-signal',
        'htmlOptions' => array('class' => 'bootstrap-widget-table')
    ))
    ?>
    <div style="float: left; padding: 10px;">
        <div style="float: left; padding: 10px;">
        <?php
        echo $form->textFieldRow($model, 'apiUsername', array('prepend' => '<i class="icon-asterisk"></i>'));
        echo $form->textFieldRow($model, 'apiPassword', array('prepend' => '<i class="icon-wrench"></i>'));
        ?>
        </div>
        <div style="float: left; padding: 10px;">
        <?php echo $form->textFieldRow($model, 'apiSignature', array('prepend' => '<i class="icon-wrench"></i>')); ?>

        </div><br style="clear:both">
        <?php  echo $form->toggleButtonRow($model, 'enable_payments_paypal');  ?>
    </div>
    <?php $this->endWidget() ?>
            </td></tr><tr>
            <td valign="top" valign="top" width="550">
     <?php
    $this->beginWidget('bootstrap.widgets.TbBox', array(
        'title' => 'FAXAGE faxing system',
        'headerIcon' => 'icon-print',
        'htmlOptions' => array('class' => 'bootstrap-widget-table')
    ))
    ?>
    <div style="float: left; padding: 10px;">
        <?php
        echo $form->textFieldRow($model, 'efax_username', array('prepend' => '<i class="icon-user"></i>'));
        echo $form->textFieldRow($model, 'efax_password', array('prepend' => '<i class="icon-wrench"></i>'));
        ?>
        <?php  echo $form->toggleButtonRow($model, 'enable_efax');  ?>
    </div>
                <div style="float: left; padding: 10px;">
                    <?php echo $form->textFieldRow($model, 'efax_login_id', array('prepend' => '<i class="icon-asterisk"></i>')); ?>
                    <?php echo $form->textFieldRow($model, 'vendor_fax', array('prepend' => '<i class="icon-info-sign"></i>', 'disabled'=>true)); ?>
                </div>
    <?php $this->endWidget() ?>
            </td>
            <td valign="top">
                <?php
    $this->beginWidget('bootstrap.widgets.TbBox', array(
        'title' => 'Google Cloud Print',
        'headerIcon' => 'icon-print',
        'htmlOptions' => array('class' => 'bootstrap-widget-table')
    ))
    ?>
    <div style="float: left; padding: 10px;">
        <?php
        echo $form->textFieldRow($model, 'gcp_email', array('prepend' => '<i class="icon-user"></i>'));
        echo $form->textFieldRow($model, 'gcp_pass', array('prepend' => '<i class="icon-wrench"></i>'));
        ?>
        <?php  echo $form->toggleButtonRow($model, 'enable_gcp');  ?>
    </div>
    <div style="float: left; padding: 10px;">
        <div id="gcp-printer-id">
        <?php echo $form->textFieldRow($model, 'gcp_printer_id', array('prepend' => '<i class="icon-wrench"></i>')); ?>
            </div>
        <?php $this->widget('bootstrap.widgets.TbButton', array('url' => '#', 'label' => Yii::t('_', 'Get printer list'), 'icon' => 'envelope','id'=>'wpmm-admin-get-printers')); ?>
    </div>
    <?php $this->endWidget() ?>
                </td>
        </tr>
    </table>
    <?php
    $this->beginWidget('bootstrap.widgets.TbBox', array(
        'title' => 'Working Hours',
        'headerIcon' => 'icon-time',
        'htmlOptions' => array('class' => 'bootstrap-widget-table')
    ))
    ?><?php $timescale =  Helpers::GetHours(); ?>
    <div class="inrfrm">
        <table border="0">
            <tr>
                <th width="85">
                    <b>Day</b>
                </th>
                <th width="230">
                    <b>From</b>
                </th>
                <th width="240">
                    <b>To</b>
                </th>
            </tr>
            <tr>
                <td valign="middle">
                    <?php echo CHtml::checkBox("MMSettingsForm[work_time][0][active]", (bool)$worktime[0]['active'], array('uncheckValue' => 0)); ?> Sunday</td>
                <td valign="top">
                    <?php echo CHtml::dropDownList("MMSettingsForm[work_time][0][from]", $worktime[0]['from'], $timescale); ?></td>
                <td valign="top">
                    <?php echo CHtml::dropDownList("MMSettingsForm[work_time][0][to]", $worktime[0]['to'], $timescale); ?></td>
            </tr>
            <tr>
                <td valign="middle">
                    <?php echo CHtml::checkBox("MMSettingsForm[work_time][1][active]", (bool)$worktime[1]['active'], array('uncheckValue' => 0)); ?> Monday</td>
                <td valign="top">
                    <?php echo CHtml::dropDownList("MMSettingsForm[work_time][1][from]", $worktime[1]['from'], $timescale); ?></td>
                <td valign="top">
                    <?php echo CHtml::dropDownList("MMSettingsForm[work_time][1][to]", $worktime[1]['to'], $timescale); ?></td>
            </tr>
            <tr>
                <td valign="middle">
                    <?php echo CHtml::checkBox("MMSettingsForm[work_time][2][active]", (bool)$worktime[2]['active'], array('uncheckValue' => 0)); ?> Tuesday</td>
                <td valign="top">
                    <?php echo CHtml::dropDownList("MMSettingsForm[work_time][2][from]", $worktime[2]['from'], $timescale); ?></td>
                <td valign="top">
                    <?php echo CHtml::dropDownList("MMSettingsForm[work_time][2][to]", $worktime[2]['to'], $timescale); ?></td>
            </tr>
            <tr>
                <td valign="middle">
                    <?php echo CHtml::checkBox("MMSettingsForm[work_time][3][active]", (bool)$worktime[3]['active'], array('uncheckValue' => 0)); ?> Wednesday</td>
                <td valign="top">
                    <?php echo CHtml::dropDownList("MMSettingsForm[work_time][3][from]", $worktime[3]['from'], $timescale); ?></td>
                <td valign="top">
                    <?php echo CHtml::dropDownList("MMSettingsForm[work_time][3][to]", $worktime[3]['to'], $timescale); ?></td>
            </tr>
            <tr>
                <td valign="middle">
                    <?php echo CHtml::checkBox("MMSettingsForm[work_time][4][active]", (bool)$worktime[4]['active'], array('uncheckValue' => 0)); ?> Thursday</td>
                <td valign="top">
                    <?php echo CHtml::dropDownList("MMSettingsForm[work_time][4][from]", $worktime[4]['from'], $timescale); ?></td>
                <td valign="top">
                    <?php echo CHtml::dropDownList("MMSettingsForm[work_time][4][to]", $worktime[4]['to'], $timescale); ?></td>
            </tr>
            <tr>
                <td valign="middle">
                    <?php echo CHtml::checkBox("MMSettingsForm[work_time][5][active]", (bool)$worktime[5]['active'], array('uncheckValue' => 0)); ?> Friday</td>
                <td valign="top">
                    <?php echo CHtml::dropDownList("MMSettingsForm[work_time][5][from]", $worktime[5]['from'], $timescale); ?></td>
                <td valign="top">
                    <?php echo CHtml::dropDownList("MMSettingsForm[work_time][5][to]", $worktime[5]['to'], $timescale); ?></td>
            </tr>
            <tr>
                <td valign="middle">
                    <?php echo CHtml::checkBox("MMSettingsForm[work_time][6][active]", (bool)$worktime[6]['active'], array('uncheckValue' => 0)); ?> Saturday</td>
                <td valign="top">
                    <?php echo CHtml::dropDownList("MMSettingsForm[work_time][6][from]", $worktime[6]['from'], $timescale); ?></td>
                <td valign="top">
                    <?php echo CHtml::dropDownList("MMSettingsForm[work_time][6][to]", $worktime[6]['to'], $timescale); ?></td>
            </tr>
        </table>
        <?php echo $form->dropDownListRow($model, 'timezone', Helpers::getTimezone()); ?>
    </div>
    <?php $this->endWidget() ?>
    <?php
    $this->beginWidget('bootstrap.widgets.TbBox', array(
        'title' => 'Display',
        'headerIcon' => 'icon-comment',
        'htmlOptions' => array('class' => 'bootstrap-widget-table')
    ))
    ?>
    <div class="inrfrm">
        <span class="cool_heding">Content</span><hr>
        <div style="float: left; padding: 10px;">
            <table border="0">
                <tr>
                    <td>
                        <?php echo $form->dropDownListRow($model, 'item_sort_field', array('id' => 'Added', 'price' => 'Price', 'name' => 'Name')); ?>
                    </td>
                    <td>
                        <?php echo $form->dropDownListRow($model, 'item_sort_order', array('ASC' => 'Increasing', 'DESC' => 'Decreasing'), array('style' => 'width:120px;')); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo $form->dropDownListRow($model, 'cat_sort_field', array('id' => 'Added', 'name' => 'Name')); ?>
                    </td>
                    <td>
                        <?php echo $form->dropDownListRow($model, 'cat_sort_order', array('ASC' => 'Increasing', 'DESC' => 'Decreasing'), array('style' => 'width:120px;')); ?>
                    </td>
                </tr>
            </table>
        </div>
        <div style="float: left; padding: 10px;">
            <table border="0" cellpadding="4">
                <tr>
                    <td>
                        <?php  echo $form->toggleButtonRow($model, 'items_pict');  ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php  echo $form->toggleButtonRow($model, 'cat_pict');  ?>
                    </td>
                </tr>
            </table>
        </div>
        <br style="clear:both">
        <span class="cool_heding">Shopping cart</span><hr>
        <div style="padding: 10px;" align="left">
            <table border="0">
                <tr>
                    <td>
                        <?php
                        echo $form->dropDownListRow(
                                $model,
                                'cart_position',
                                array(
                                    'inside-left' => 'Inside left',
                                    'inside-right' => 'Inside right',
                                    'outside-left' => 'Outside left',
                                    'outside-right' => 'Outside right'
                                ),
                                array('style' => 'width:150px')
                        );
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        <div style="padding: 10px;" align="left">
            <?php echo $form->dropDownListRow($model, 'item_mode', array('0' => 'As cards', '1' => 'As table')); ?>
            <?php
                echo $form->toggleButtonRow($model, 'enable_delivery');
            ?>
            <?php
                echo $form->toggleButtonRow($model, 'enable_social');
            ?>
            <?php
                echo $form->toggleButtonRow($model, 'powered_by');
            ?>
        </div>
    </div>
    <?php $this->endWidget() ?>
</div>
<script>
jQuery('#wpmm-admin-get-printers').on('click', function(){
        var details = jQuery('#wpmm-item-details');
        jQuery.ajax({
            url: '<?php echo MM_AJAX_URL ?>',
            data: {
                'action': 'admin',
                'gcp_get_printers' : 1,
                'login': jQuery('#MMSettingsForm_gcp_email').val(),
                'pass': jQuery('#MMSettingsForm_gcp_pass').val(),
                'print_id' : jQuery('#MMSettingsForm_gcp_printer_id').val(),
                '<?php echo Yii::app()->request->csrfTokenName ?>':'<?php echo Yii::app()->request->csrfToken ?>'
            },
            type: "POST",
            dataType: "html",
            beforeSend: function(){
                jQuery('#gcp-printer-id').append('<img style="height:16px;" class="ajax_loader_settings" src="<?php echo Yii::app()->baseUrl .'/images/icons/ajax-loader.gif'?>">');
            },
            error: function(){
                alert('<?php echo Yii::t('_', 'Your request failed!'); ?>');
            },
            success: function(data) {
                if (data) {
                    jQuery('#gcp-printer-id').html(data);
                }
            }
        });
            return false;
    });
</script>
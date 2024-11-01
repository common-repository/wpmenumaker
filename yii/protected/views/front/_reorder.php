<div id="wpmm-item-details-header">
    <p id="wpmm-item-details-name">View previous orders</p>
</div>
<div id="wpmm-item-details-content">
    <div id="wpmm-item-details-content-right" class="noimage" style="padding-top: 6px; padding-bottom: 3px; line-height: 15px;">
        Last orders:
    </div>
</div>
<div id="wpmm-item-details-more" style="height: 300px">
<?php
$counter = 0;
if(!empty($orders)){
    foreach ($orders as $order){
        $reitems = NULL;
        $params =  unserialize($order->params);
        if($params['reorder'] == 1){
            ?>
        <a href="#" class="wpmm-cool-span wpmm-order-reorder-toggle">Order #<?php echo  $order->id;?>, submited: <?php echo date("m/d/Y", strtotime($order->time_ordered)); ?></a>
        <div class="wpmm-reorder-container" <?php if($counter != 0){ echo 'style="display: none"';} else $counter=1; ?>>
            <?php
            foreach($order->items as $item){
                if(MMItem::model()->exists('id = "'.$item->itemid.'" AND published = 1')){
                    $reitems[]=$item;
                }
            }
            if(!empty($reitems)){
                ?>
            <hr class="wpmm-form-hr">
            <table border="0" width="100%">
                <tr style="border-bottom: 2px solid black">
                    <td style="text-align: center"><b>Qty.</b></td>
                    <td style="text-align: center"><b>Item</b></td>
                    <td style="text-align: center" width="85"><b>Re-order</b></td>
                </tr>
                    <?php
               foreach($reitems as $reitem){
                   $attr_list = array();
                   $uniqueid=uniqid();
                   $realitem=  Helpers::getItemById($reitem->itemid);
                   $attributes = unserialize($reitem->attribs);
                   $attr_id=array();
                   foreach($attributes as $att){
                                    if (is_array($att)) {
                                       $attr_id[]=$att['orig_id'];
                                    }
                                }
                   if(!empty($realitem->params)){
                        foreach ($realitem->params as $group){
                            if($group->type == 1){
                                if(!empty($group->attribs)){
                                    foreach($group->attribs as $attr){
                                        if(in_array($attr->id,$attr_id)){
                                            $attr_list[]=$attr;
                                            break;
                                        } else if($attr->checked_id){
                                            $attr_list[]=$attr;
                                            break;
                                        }
                                    }
                                }
                            } else {
                                if(!empty($group->attribs)){
                                    foreach($group->attribs as $attr){
                                        if(in_array($attr->id,$attr_id)){
                                            $attr_list[]=$attr;
                                        }
                                    }
                                }
                            }
                        }
                   }
                   ?>
                <tr>
                    <td style="text-align: center" valign="top"><?php echo $attributes['quantity']; ?></td>
                    <td valign="top"><b><?php echo $realitem->name; ?></b>
                        <?php if(!empty($attr_list)){
                            echo ' ( ';
                            $first=0;
                                foreach($attr_list as $attr){
                                    if($first == 0) {
                                        echo $attr->name.'  $'.number_format((float) $attr->price, 2, '.', ' ');
                                        $first=1;
                                    } else {
                                        echo ' | '.$attr->name.'  $'.number_format((float) $attr->price, 2, '.', ' ');
                                    }
                                }
                            echo ' )';
                            }?>
                    </td>
                    <td style="text-align: center" valign="top"><a href="#" class="wpmm-green-btn" id="<?php echo 'add_'.$uniqueid ?>">Add</a>
                        <script>
                            <?php
                            $pass_params = array(
                                'id'=>$realitem->id,
                                'price'=>$realitem->price,
                                'instructions'=>'',
                                'quantity'=>$attributes['quantity'],
                                'attr'=>array()
                            );
                            if(!empty($attr_list)) {
                                foreach($attr_list as $att){
                                        $pass_params['attr'][$att->id]=number_format((float) $att->price, 2);
                                }
                            }
                            ?>
                        jQuery('#<?php echo 'add_'.$uniqueid ?>').on('click', function() {
                            var item = <?php echo CJSON::encode($pass_params); ?>;
                            var data = {
                                'action': 'front',
                                'additem': item,
                                '<?php echo Yii::app()->request->csrfTokenName ?>':'<?php echo Yii::app()->request->csrfToken ?>'
                            };
                            jQuery.ajax({
                                url: '<?php echo MM_AJAX_URL ?>',
                                data: data,
                                type: "POST",
                                dataType: "json",
                                beforeSend: function() {
                                    jQuery('#wpmm-item-loading').css({'top':'50%','left':'50%','margin-left':'-'+24+'px'}).show();
                                },
                                success: function(data) {
                                    jQuery('#wpmm-item-details-close').click();
                                    refresh_order();
                                    if (jQuery('#wpmm-form-showbutton').length)
                                        jQuery('#wpmm-form-showbutton').click();
                                    jQuery('#wpmm-item-loading').hide();
                                    jQuery('#wpmm-item-details').show();
                                }
                            });
                            return false;
                            });
                    </script>
                    </td>
                </tr>
                   <?php
               }
               ?>
             </table>
               <?php
            } else {
               echo '<strong class="wpmm-color-text">There are no available items to re-order.</strong>';
            }
            ?>
            <hr class="wpmm-form-hr">
        </div>
        <?php
        }
    }
} else {
    ?>
        <strong class="wpmm-color-text">There are no available orders to re-order.</strong>
        <?php
}
?>
</div>
<div id="wpmm-item-details-footer">
    <a href="#" id="wpmm-item-details-close">Close</a>
</div>
<script>
    jQuery('#wpmm-item-details-more').mCustomScrollbar();
    jQuery(document).trigger('resize');
    jQuery('#wpmm-item-details').draggable({ handle: "#wpmm-item-details-header" });
    jQuery('#wpmm-item-details-close').on('click', function() {
        jQuery('#wpmm-item-details').hide();
        return false;
    });
    jQuery('.wpmm-order-reorder-toggle').on('click', function() {
        jQuery(this).next().slideToggle('normal', function() {
            jQuery('#wpmm-item-details-more').mCustomScrollbar('update');
        });
        return false;
    });
</script>

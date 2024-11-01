<legend>Import DB</legend>
<div class="mm-container">
    <?php
    $this->beginWidget('bootstrap.widgets.TbBox', array(
        'title' => 'Import from WPMenuMakerLite',
        'headerIcon' => 'icon-hdd',
        'htmlOptions' => array('class' => 'bootstrap-widget-table')
    ))
    ?>
    <?php
    $yesflag=false;
    $pluginpath=ABSPATH . 'wp-content/plugins/wpmenumakerlite/wpmmlite.php';
    $pluginconfig=ABSPATH . 'wp-content/plugins/wpmenumakerlite/wpmmlite-config.php';
    if (is_file($pluginpath) && is_file($pluginconfig)) {
        include_once $pluginconfig;
        global $wpdb;
        $tables = array(
            'attributes',
            'categories',
            'category_items',
            'groups',
            'items',
            'menu_categories',
            'menus',
        );
        $names = array();
        foreach ($tables as $table) {
            $names[$table] = MMLITE_DB_PREFIX . MMLITE_TABLE_PREFIX . $table;
        }

        foreach ($names as $table => $name) {
            if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . $name . "'")) == 1) {
                $test = Yii::app()->db->createCommand()->select('COUNT(*) as num')->from($name)->queryRow();
                if($test['num'] > 0) {
                  $yesflag=true;
                }
            }
        }
    }
    ?>
    <div class="inrfrm">
        <table>
            <tr>
                <td>
                    <?php
                    if($yesflag == false){
                             $this->widget('bootstrap.widgets.TbButton', array(
                                 'type' => 'primary',
                                 'buttonType' => 'submit',
                                 'label' => 'Import Database',
                                 'id' => 'wpmm-import-db',
                                 'htmlOptions' => array('class'=>'disabled')
                             ));
                    } else {
                              $this->widget('bootstrap.widgets.TbButton', array(
                                 'type' => 'primary',
                                 'buttonType' => 'submit',
                                 'label' => 'Import Database',
                                 'id' => 'wpmm-import-db',
                                 'htmlOptions' => array()
                             ));
                    }
                ?>
                </td>
                <td>
                    <p style="padding-left: 5px;">
                    <?php echo CHtml::image(Yii::app()->baseUrl . "/images/icons/ajax-loader.gif", "", array(
                        "style" => "height:16px; display:none",
                        'class' => 'ajax_loader_settings'
                            ))
                    ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>
    <?php $this->endWidget() ?>
</div>
<script>
    <?php if($yesflag == false){ ?>
        jQuery(document).ready(function(){
            jQuery('#wpmm-import-db').bind('click', false);
            });
    <?php } else { ?>
        jQuery("#wpmm-import-db").on('click', function() {
            if(confirm('Importing WPMenuMakerLite database will delte all previos data, continue ?')){
                jQuery.ajax({
                    url: '<?php echo MM_AJAX_URL ?>',
                    data: {
                        'action': 'admin',
                        'importdb': true,
                        '<?php echo Yii::app()->request->csrfTokenName ?>':'<?php echo Yii::app()->request->csrfToken ?>'
                    },
                    type: "POST",
                    dataType: "html",
                    beforeSend: function() {
                        jQuery(".ajax_loader_settings").fadeIn();
                    },
                    error: function(){
                        jQuery(".ajax_loader_settings").fadeOut();
                        alert('There where errors!');
                    },
                    success: function(data) {
                            jQuery(".ajax_loader_settings").fadeOut();
                    }
                });
                return false;
            } else {
                return false;
            }
        });
        <?php } ?>
    </script>
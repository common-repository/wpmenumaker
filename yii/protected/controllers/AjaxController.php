<?php

class AjaxController extends Controller {

    public $defaultAction = 'front';
    public $order;

    public function init() {
        Yii::app()->bootstrap;
        date_default_timezone_set(MMSettingsForm::getParam('timezone'));
        parent::init();
    }

    public function actionAdmin() {

        if (Yii::app()->request->isAjaxRequest && $_POST[Yii::app()->request->csrfTokenName] == Yii::app()->getRequest()->getCsrfToken()) {

            if (isset($_POST['ajax_validation'])) {
                $id = (int) $_POST['ajax_validation'];
                $model_name = $_POST['model_name'];
                if ($id > 0) {
                    $model = call_user_func(array($model_name, 'model'))->findByPk($id);
                    if ($model === null) {
                        throw new CHttpException(404, 'The requested page does not exist.');
                        return $model;
                    }
                } else {
                    $model = new $model_name;
                }
                if (isset($_POST['ajax'])) {
                    echo CActiveForm::validate($model);
                }
                die();
            }

            else if(isset($_POST['gcp_get_printers'])){
                 $gcp_login = trim(strip_tags($_POST['login']));
                    $gcp_pass = trim(strip_tags($_POST['pass']));
                    if (!empty($gcp_login) && !empty($gcp_pass)) {
                        $gcp = new MMGCP();
                        if ($gcp->loginToGoogle($gcp_login, $gcp_pass)) {
                            $printers = $gcp->getPrinters();
                            $printers = CHtml::listData($printers, 'id', 'name');
                            $tempmodel=new MMSettingsForm();
                            $tempmodel->gcp_printer_id = trim(strip_tags($_POST['print_id']));
                            echo CHtml::activeLabel($tempmodel, 'gcp_printer_id');
                            echo CHtml::activeDropDownList($tempmodel, 'gcp_printer_id', $printers);
                        } else {
                            echo Yii::t('_','Login is incorrect');
                        }
                    } else
                        echo Yii::t('_','Login is incorrect');
                    die();
            }

            else if(isset($_POST['importdb'])){
                $pluginconfig=ABSPATH . 'wp-content/plugins/wpmenumakerlite/wpmmlite-config.php';
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
                $namesfull = array();
                foreach ($tables as $table) {
                    $names[$table] = MMLITE_DB_PREFIX . MMLITE_TABLE_PREFIX . $table;
                }
                foreach ($tables as $table) {
                    $namesfull[$table] = MM_DB_PREFIX . MM_TABLE_PREFIX . $table;
                }

                foreach ($names as $table => $name) {
                    if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . $name . "'")) == 1) {
                        $test = Yii::app()->db->createCommand()->select('COUNT(*) as num')->from($name)->queryRow();
                        if($test['num'] > 0) {
                            $namesfull=MM_DB_PREFIX . MM_TABLE_PREFIX .$table;
                            if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . $namesfull . "'")) == 1) {
                               Yii::app()->db->createCommand()->truncateTable($namesfull);
                               Yii::app()->db->createCommand("INSERT $namesfull SELECT * FROM $name")->query();
                            }
                        }
                    }
                }
                die();
            }

            elseif (isset($_POST['group_items'])) {
                $model = new MMGroup();
                $divid = uniqid();
                if (isset($_POST['gid']) && ($_POST['gid'] > 0) && isset($_POST['iid']) && ($_POST['iid'] > 0)) {
                    $gid = $_POST['gid'];
                    $iid = $_POST['iid'];
                } else {
                    $gid = 'group' . $divid;
                    $iid = 0;
                }
                echo '<div class="grpfrm">';
                echo "<div id='group{$divid}'><label>Group Name: </label>";
                echo CHtml::activeTextField($model, "name[]") . '<label> Group type: </label>';
                echo CHtml::activeDropDownList($model, "type[]", $model->getTypeList());
                echo CHtml::activeHiddenField($model, "id[]", array('value' => $gid));
                echo CHtml::activeHiddenField($model, "item_id[]", array('value' => $iid));
                $this->widget('bootstrap.widgets.TbButton', array(
                    'label' => '',
                    'type' => 'danger',
                    'size' => 'mini',
                    'icon' => 'remove white',
                    'url' => '',
                    'id' => 'group' . $divid,
                    'htmlOptions' => array('onClick' => "jQuery('#group{$divid}').parent().remove();", 'style' => 'margin:2px; margin-bottom:10px;', 'rel' => 'tooltip', 'title' => 'Remove Group',
                    ),
                ));
                echo '</div>';
                echo '<div id="' . $divid . '" class="wp-attrib"></div>';
                $uid = uniqid('form');
                echo "<div id='{$uid }'>";
                echo Chtml::hiddenField('g_id', 'group' . $divid);
                echo Chtml::hiddenField('attribure_items', 'items');
                $this->widget('bootstrap.widgets.TbButton', array(
                    'label' => 'Add Item',
                    'url' => '',
                    'htmlOptions' => array('onClick' => "jQuery.ajax({'type':'POST','success':function( data ) {
                            // handle return data
                            jQuery('#{$divid}').append(data);
                            var actual = jQuery('#{$divid}').parent().find('select');
                                if(jQuery(actual).find(':selected').text() == 'Single-choise'){
                                if(jQuery('#{$divid}').parent().find('#attr input:checked').length == 0) {
                                jQuery('#{$divid}').parent().find('#attr input:checkbox').attr('checked', false);
                                jQuery('#{$divid}').parent().find('#attr input:checkbox:first').attr('checked', true);
                                    }
                            }
                          },
                        'url':'" . MM_AJAX_URL . "','cache':false,
                        'data':jQuery('#{$uid}').children().serialize()+'&action='+ encodeURIComponent('admin')+'&" . Yii::app()->request->csrfTokenName . "='+encodeURIComponent('" . Yii::app()->request->csrfToken . "')
                        });"
                    ),
                ));
                echo '</div></div>';
            }

            elseif (isset($_POST['attribure_items'])) {
                if (isset($_POST['g_id'])) {
                    $g_id = $_POST['g_id'];
                }
                $model = new MMAttribute();
                $divid = uniqid('item');
                echo "<div id='attr'><label>Name: </label>";
                echo CHtml::activeTextField($model, "name[]") . '<label>Price: </label>';
                echo CHtml::activeTextField($model, "price[]", array('style' => 'width:50px;')) . ' ';
                echo CHtml::CheckBox("checked_id_" . $divid, false, array('uncheckValue' => 0));
                echo CHtml::activeHiddenField($model, "group_id[]", array('value' => $g_id));
                echo CHtml::activeHiddenField($model, "id[]", array('value' => $divid));
                $this->widget('bootstrap.widgets.TbButton', array(
                    'label' => '',
                    'type' => 'danger',
                    'size' => 'mini',
                    'icon' => 'remove white',
                    'url' => '',
                    'id' => $divid,
                    'htmlOptions' => array('onClick' => "jQuery('#{$divid}').parent().remove();", 'style' => 'margin:6px;', 'rel' => 'tooltip', 'title' => 'Remove Attribute',
                    ),
                ));
                echo '<br></div>';
                echo '<script type="text/javascript">';
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
                echo $script;
                echo '</script>';
            }

            elseif (isset($_POST['MMSettingsForm'])) {
                $settings = new MMSettingsForm();
                $settingsvalues = Yii::app()->db->createCommand()
                        ->select('name, value')
                        ->from(Wpmm::getTableNames('settings'))
                        ->where("name IN('" . implode("','", array_keys($settings->attributes)) . "')")
                        ->queryAll();

                foreach ($settingsvalues as $setting) {
                    $settings->{$setting['name']} = $setting['value'];
                }
                foreach ($settings->attributes as $name => $value) {
                    if ($name == 'themes' || $name == 'zip_list')
                        continue;
                    if ($name == 'work_time') {
                        $settings->work_time = serialize($_POST['MMSettingsForm']['work_time']);
                        continue;
                    }
                    if($name == 'license_key'){
                        $settings->license_key = trim($_POST['MMSettingsForm']['license_key']);
                        continue;
                    }
                    $settings->$name = is_array($_POST['MMSettingsForm'][$name]) ? $_POST['MMSettingsForm'][$name][0] : $_POST['MMSettingsForm'][$name];
                }
                $settings->save();
            }

            elseif (isset($_POST['addzipcode'])) {
                $settings = new MMSettingsForm('addzipcode');
                $settings->zipcode = $_POST['addzipcode'];
                $ziplist = unserialize(MMSettingsForm::getParam('zip_list'));
                if ($settings->validate(array('zipcode'))) {
                    $ziplisttemp = $ziplist;
                    $ziplist[] = $settings->zipcode;
                    $ziplist = array_unique($ziplist);
                    if ($ziplist != $ziplisttemp) {
                        sort($ziplist, SORT_NUMERIC);
                        $settings->zip_list = serialize($ziplist);
                        if (!$settings->save('zip_list'))
                            exit('ZIP code saving error');
                    }
                }
                $this->renderPartial(
                        '//admin/_ziptable', array(
                    'ziplist' => $ziplist,
                    'error' => $settings->errors,
                ));
            }

            elseif (isset($_POST['reset_pass'])) {
                $username = $_POST['reset_pass'];
                        $user = MMCustomer::model()->findByPk($username);
                        if ($user != NULL) {
                            $newpass = Helpers::randomPassword();
                            $user->password = $newpass;
                            if ($user->save()) {
                                YiiBase::import('ext.YiiMail.YiiMailMessage');
                                $shopemail = MMSettingsForm::getParam('vendor_email');
                                if(empty($shopemail)) $shopemail='noemail@noemail.mail';
                                $shopname = MMSettingsForm::getParam('vendor_name');
                                if(empty($shopname)) $shopname='Unknown shop';
                                $message = new YiiMailMessage;
                                $message->setBody('<b>Good Day</b><br>You requested password resent from our service. Your new password is: <br><b>' . $newpass . '</b><br>Thank you for using our service!'.Helpers::shopDetails(), 'text/html', 'utf-8');
                                $message->addTo($user->c_mail);
                                $message->setFrom(array($shopemail => $shopname));
                                $message->setReplyTo(array($shopemail => $shopname));
                                $message->setSubject("Password reset for: " . $user->c_mail);
                                Yii::app()->mail->send($message);
                                echo 'succsess';
                            }
                        }
                        Yii::app()->end();
            }

            elseif (isset($_POST['resend_bill'])) {
                $id = $_POST['resend_bill'];
                        $model = MMOrder::model()->findByPk($id);
                        if ($model != NULL) {
                                Helpers::sendOrderToEmail($model, $model->customer->c_mail,false);
                                echo 'succsess';
                            }
                        Yii::app()->end();
            }

            elseif (isset($_POST['refresh_key'])) {
                echo $this->_res;
                if (substr($this->_res, 3, 6) !== 'Active')
                    echo '<a href="http://wpmenumaker.com/pricing" target="_blank">Purchase WPMenuMaker</a>';
                Yii::app()->end();
            }

            elseif (isset($_POST['removezipcode'])) {
                $settings = new MMSettingsForm();
                $ziplist = unserialize(MMSettingsForm::getParam('zip_list'));
                $ziplist = array_diff($ziplist, array((int) $_POST['removezipcode']));
                $settings->zip_list = serialize($ziplist);
                if ($settings->save('zip_list')) {
                    $this->renderPartial(
                            '//admin/_ziptable', array(
                        'ziplist' => $ziplist,
                    ));
                    Yii::app()->end();
                } else {
                    exit('ZIP code saving error');
                }
            }
        } elseif (empty(Yii::app()->session->sessionId)) {
            echo "Your session has expired. Please refresh page.";
        } else {
            echo 'CSRF verification failed';
        }
        Yii::app()->end();
    }

    public function actionFront() {

        if (Yii::app()->request->isAjaxRequest && $_POST[Yii::app()->request->csrfTokenName] == Yii::app()->request->getCsrfToken()) {

            $this->order = Yii::app()->session['wpmm_order'];


            if (isset($_POST['vendor_info'])) {
                $params = MMSettingsForm::getParams();
                $options['name'] = $params['vendor_name'];
                $options['state'] = $params['vendor_state'];
                $options['city'] = $params['vendor_city'];
                $options['street'] = $params['vendor_street'];
                $options['phone'] = $params['vendor_phone'];
                $options['fax'] = $params['vendor_fax'];
                $options['email'] = $params['vendor_email'];
                $options['zip'] = $params['zip_self'];
                $options['delivery_charge'] = $params['delivery_charge'];
                $options['min_order'] = $params['min_order'];
                $this->renderPartial('//front/_deliverydetails', array('options' => $options));
            }

            elseif (isset($_POST['forgot_pass'])) {
                $username = trim(strip_tags($_POST['forgot_pass']));
                $errors = '';
                $info = '';
                if ($username != 'notemailkey') {
                    $validator = new CEmailValidator;
                    if ($validator->validateValue($username)) {
                        $user = MMCustomer::model()->find(array('condition' => 'c_mail="' . $username . '"'));
                        if ($user == NULL) {
                            $errors = 'Invalid user email address!';
                        } else {
                            $newpass = Helpers::randomPassword();
                            $user->password = $newpass;
                            if ($user->save()) {
                                YiiBase::import('ext.YiiMail.YiiMailMessage');
                                $shopemail = MMSettingsForm::getParam('vendor_email');
                                $shopname = MMSettingsForm::getParam('vendor_name');
                                $message = new YiiMailMessage;
                                $message->setBody('<b>Good Day</b><br>You requested password resent from our service. Your new password is: <br><b>' . $newpass . '</b><br>Thank you for using our service!'.Helpers::shopDetails(), 'text/html', 'utf-8');
                                $message->addTo($user->c_mail);
                                $message->setFrom(array($shopemail => $shopname));
                                $message->setReplyTo(array($shopemail => $shopname));
                                $message->setSubject("Password reset for: " . $user->c_mail);
                                Yii::app()->mail->send($message);
                                $info = 'New password has been successfully sent to your email';
                            }
                        }
                    } else {
                        $errors = 'Not valid user e-mail!' . $username;
                    }
                }
                $this->renderPartial('//front/_remindpass', array('errors' => $errors, 'info' => $info));
            }

            elseif (isset($_POST['login_customer'])) {
                $username = trim(strip_tags($_POST['login_customer']['username']));
                $password = trim(strip_tags($_POST['login_customer']['password']));
                $model = new MMLoginForm;
                $model->password = $password;
                $model->username = $username;
                if ($model->validate() && $model->login()) {
                    $order = Yii::app()->session['wpmm_order'];
                    if (isset($order['address'])) {
                        unset($order['address']);
                        $order['cart_form']['address'] = '';
                        Yii::app()->session['wpmm_order'] = $order;
                    }
                } else {
                    echo 'Wrong Username or password! ';
                }
            } else if (isset($_POST['logout_customer'])) {
                Yii::app()->user->logout();
                Yii::app()->session->open();
                Yii::app()->session[Yii::app()->request->csrfTokenName] = Yii::app()->request->csrfToken;
            }

            elseif (isset($_POST['add_address'])) {
                $model = new MMAddress();
                $address_id = trim(strip_tags($_POST['add_address']));
                if ($address_id != 0 && is_numeric($address_id)) {
                    $editmode = 'db';
                    $model = MMAddress::model()->findByPk($address_id);
                    if ($model == NULL) {
                        $editmode = 'new';
                    }
                } else if (strlen(strpos($address_id, 'address')) > 0) {
                    $order = Yii::app()->session['wpmm_order'];
                    foreach ($order['address'] as $addrr) {
                        if ($addrr['id'] == $address_id) {
                            $model->attributes = $addrr;
                            $model->id = $address_id;
                            $editmode = 'session';
                            break;
                        } else {
                            $editmode = 'new';
                        }
                    }
                } else {
                    $editmode = 'new';
                }
                if (isset($_POST['MMAddress'])) {
                    $model->attributes = $_POST['MMAddress'];
                    $order = Yii::app()->session['wpmm_order'];
                    if ($model->validate()) {
                        if (Yii::app()->user->isGuest) {
                            Yii::app()->session['wpmm_order'] = Helpers::addSessionAddress($order, $_POST['MMAddress']);
                        } else {
                            $model->customer_id = Yii::app()->user->id;
                            if($model->save()){
                                $order['cart_form']['address']='wppp'.$model->id;
                                Yii::app()->session['wpmm_order'] = $order;
                            }
                        }
                        return true;
                    }
                }
                $this->renderPartial('//front/_addaddress', array('model' => $model, 'editmode' => $editmode));
            }

            elseif (isset($_POST['add_address_delete'])) {
                $order = Yii::app()->session['wpmm_order'];
                $address_id = trim(strip_tags($_POST['add_address_delete']));
                if ($address_id != 0 && is_numeric($address_id)) {
                    $model = MMAddress::model()->findByPk($address_id);
                    if ($model === null) {
                        throw new CHttpException(404, 'The requested page does not exist.');
                        return $model;
                    } else {
                        $model->delete();
                        $order = Yii::app()->session['wpmm_order'];
                        $order['cart_form']['address'] = 'delete';
                        Yii::app()->session['wpmm_order'] = $order;
                    }
                } else if (strlen(strpos($address_id, 'address')) > 0) {
                    foreach ($order['address'] as $key => $addrr) {
                        if ($addrr['id'] == $address_id) {
                            unset($order['address'][$key]);
                            $order['cart_form']['address'] = 'delete';
                            Yii::app()->session['wpmm_order'] = $order;
                            return true;
                        }
                    }
                } else {
                    throw new CHttpException(404, 'The requested page does not exist.');
                }
            }

            elseif (isset($_POST['continueorder'])) {

                $orderammount = (float)trim(strip_tags($_POST['orderammount']));

                Yii::app()->controller->renderPartial('//front/_cardform',array('orderammount'=>$orderammount));

            }

            elseif (isset($_POST['submitorder'])) {
                $order = Yii::app()->session['wpmm_order'];
                $erros = array();
                $user = new MMCustomer();
                $rand_user_pass = Helpers::randomPassword();

                if (isset($order['errors']))
                    unset($order['errors']);
                if (isset($order['newuserform']))
                    unset($order['newuserform']);
                if (isset($order['cart_form']))
                    unset($order['cart_form']);
                if (isset($order['card_form']))
                    unset($order['card_form']);

                $failed_order = false;
                $send_register_message = false;
                $addresses_to_save = array();

                $order['cart_form']['delivery-notes'] = trim(strip_tags($_POST['data']['delivery-notes']));

                if (isset($_POST['data']['payment-type'])) {
                    $order['cart_form']['payment_type'] = trim(strip_tags($_POST['data']['payment-type']));
                } else {
                    $erros['payment'][] = 'Please select a payment method';
                    $failed_order = true;
                }

                if (isset($_POST['data']['delivery-type'])) {
                    $order['cart_form']['delivery-type'] = trim(strip_tags($_POST['data']['delivery-type']));
                } else {
                    $erros['delivery'][] = 'Please select delivery method';
                    $failed_order = true;
                }

                if (isset($_POST['data']['address']) && !empty($_POST['data']['address'])) {
                    $addr_tmp1=trim(strip_tags($_POST['data']['address']));
                    if (strlen(strpos($addr_tmp1, 'wppp')) > 0) {
                        $order['cart_form']['address'] = substr($order['cart_form']['address'], 4);
                    } else if ($addr_tmp1 == 'delete') {
                        $order['cart_form']['address'] = '';
                    } else {
                        $order['cart_form']['address'] = trim(strip_tags($_POST['data']['address']));
                    }
                } else if($order['cart_form']['delivery-type'] != 'Pickup') {
                    $erros['delivery'][] = 'Please select an address';
                    $failed_order = true;
                }

                if($order['cart_form']['delivery-type'] == 'Pickup'){
                   $order['cart_form']['address']=0;
                }

                if (isset($_POST['data']['promo_code'])) {
                    $order['cart_form']['promo_code'] = trim(strip_tags($_POST['data']['promo_code']));
                    $code=  MMCoupons::model()->find('code ="'.$order['cart_form']['promo_code'].'"');
                    if(!empty($order['cart_form']['promo_code'])){
                        if ($code == NULL) {
                                $erros['items'][] = 'Invalid promo code !';
                                $failed_order = true;
                        } else {
                            if($code->active){
                                if(!Helpers::check_in_range($code->cfrom, $code->cto, date('Y-m-d H:i:s',  time()))){
                                    $erros['items'][] = 'Promo code is out of date.';
                                    $failed_order = true;
                                }
                            } else {
                              $erros['items'][] = 'Invalid promo code !';
                              $failed_order = true;
                            }
                        }
                    }
                }

                if (isset($_POST['data']['payment-tip-value'])) {
                    $tipvalue = trim(strip_tags($_POST['data']['payment-tip-value']));
                    if (!is_numeric($tipvalue)) {
                        $erros['tips'][] = 'Tip amount must be a number.';
                        $failed_order = true;
                    }
                    if (empty($tipvalue))
                        $tipvalue = 0;
                    if (!isset($_POST['data']['payment-tip-type'])) {
                        $erros['tips'][] = 'Please select tips option.';
                        $failed_order = true;
                    }
                    $order['cart_form']['payment-tip-value'] = $tipvalue;
                    $order['cart_form']['payment-tip-type'] = trim(strip_tags($_POST['data']['payment-tip-type']));
                }

                if (!isset($order['items'])) {
                    $erros['items']['min'] = 'Please add some items.';
                    $failed_order = true;
                } else {
                    //$discount = MMSettingsForm::getParam('discount');
                    $minimumorder = MMSettingsForm::getParam('min_order');
                    //$deliveryprice = MMSettingsForm::getParam('delivery_charge');
                    //$taxrate = MMSettingsForm::getParam('tax_rate');
                    /*$summ = 0;
                    foreach ($order['items'] as $order_item) {
                        if (!Helpers::validateItemTime($order_item['id'])) {
                            $erros[$key] = 'Not available at this time.';
                            $failed_order = true;
                        }
                        $summ+=(float) $order_item['price'] * (int) $order_item['quantity'];
                        if (count($order_item['attributes'])) {
                            foreach ($order_item['attributes'] as $attr_id => $attr_price) {
                                $summ+=(float) $attr_price;
                            }
                        }
                    }*/
                    if ($minimumorder > Helpers::getOrderSubtotal()) {
                        $erros['items'][] = 'Summ of order is less then minimum order amount.';
                        $failed_order = true;
                    }
                }

                if (isset($_POST['data']['delivery-time'])) {
                    $order['cart_form']['delivery-time'] = trim(strip_tags($_POST['data']['delivery-time']));
                } else {
                    $erros['time'][] = 'Please chosse delivery time';
                    $failed_order = true;
                }

                if (isset($_POST['data']['delivery-time']) && $_POST['data']['delivery-time'] != 'asap') {
                    if(! isset($_POST['data']['order-time'])){
                       $erros['time'][] = 'Please select date and time for your order';
                       $failed_order = true;
                    }
                    if($_POST['data']['delivery-time'] == 'today'){
                       $order['cart_form']['order-time'] = date('m/d/Y').' '.trim(strip_tags($_POST['data']['order-time']));
                    } else
                    $order['cart_form']['order-time'] = trim(strip_tags($_POST['data']['order-time']));
                    if (empty($order['cart_form']['order-time'])) {
                        $erros['time'][] = 'Please select date and time for your order';
                        $failed_order = true;
                    }
                } else if(isset($_POST['data']['order-time']) || !isset($_POST['data']['order-time'])) {
                    $order['cart_form']['order-time']=mktime(0, 0, 0, date("m")-1, date("d")-1,   date("Y")-1);
                } else {
                   $erros['time'][] = 'Date or time for your order cannot be blank';
                   $failed_order = true;
                }

                if ($order['cart_form']['delivery-time'] == 'date' || $order['cart_form']['delivery-time'] == 'today') {
                    if (!empty($order['cart_form']['order-time'])){
                        if (!Helpers::validateWorkingHours($order['cart_form']['order-time'])){
                            $errors['items']['work_time'] = Helpers::getWorkingHours($order['cart_form']['order-time']);
                            $failed_order = true;
                        }
                    }
                }

                if (Yii::app()->user->isGuest) {
                    $order['newuserform']['name'] = trim(strip_tags($_POST['data']['cust_name']));
                    $order['newuserform']['surname'] = trim(strip_tags($_POST['data']['cust_surname']));
                    $order['newuserform']['c_mail'] = trim(strip_tags($_POST['data']['cust_c_mail']));
                    $order['newuserform']['phone'] = trim(strip_tags($_POST['data']['cust_phone']));
                    $user->attributes = $order['newuserform'];
                    $user->password = $rand_user_pass;

                    if (!$user->validate()) {
                        $erros['user'] = $user->getErrors();
                        $failed_order = true;
                    }

                    if (isset($order['address'])) {
                        $isadrrs = false;
                        foreach ($order['address'] as $adrr) {
                            if ($order['cart_form']['address'] == $adrr['id']) {
                                $order['cart_form']['address'] = $adrr['id'];
                                $isadrrs = true;
                                if (!Helpers::ValidateZipTable($adrr['location'])) {
                                    $erros['delivery'][] = 'Invalid zip code.';
                                    $failed_order = true;
                                }
                            }
                            $adresmodel = new MMAddress();
                            $adresmodel->city = $adrr['city'];
                            $adresmodel->state = $adrr['state'];
                            $adresmodel->address = $adrr['address'];
                            $adresmodel->location = $adrr['location'];
                            if($isadrrs){
                               $addresses_to_save = $adresmodel;
                            }
                        }
                        if ($isadrrs == false && $order['cart_form']['address'] != 0) {
                            $erros['delivery'][] = 'Please select a valid address';
                            $failed_order = true;
                        }
                    }
                } else {
                    if($order['cart_form']['address'] != 0){
                        $addresstest = MMAddress::model()->findByPk($order['cart_form']['address']);
                        if ($addresstest == NULL) {
                            $erros['delivery'][] = 'Selected address is not found.';
                            $failed_order = true;
                        } else {
                            if (!Helpers::ValidateZipTable($addresstest->location)) {
                                $erros['delivery'][] = 'Selected zip code is out of delivery range.';
                                $failed_order = true;
                            }
                        }
                    }
                }

                if ($failed_order == true) {
                    echo 'There where errors';
                    $order['errors'] = $erros;
                    Yii::app()->session['wpmm_order'] = $order;
                } else {
                    $order['errors'] = $erros;
                    Yii::app()->session['wpmm_order'] = $order;
                    Yii::app()->controller->renderPartial('//front/_checkout',array('addrs'=>$addresses_to_save));
                }
            }

            elseif (isset($_POST['itemdetails'])) {
                if (isset($_POST['itemdetails']['id'])) {
                    $id = (int) $_POST['itemdetails']['id'];
                }
                elseif (isset($_POST['itemdetails']['key'])) {
                    $item_key = (int) $_POST['itemdetails']['key'];
                    $order = Helpers::getSessionOrder();
                    $id = $order['items'][$item_key]['id'];
                }
                $criteria = new CDbCriteria(array(
                            'alias' => 'item',
                            'condition' => "item.id = $id",
                            'together' => true,
                            'with' => array(
                                'params' => array(
                                    'alias' => 'groups',
                                    'order' => 'groups.id ASC',
                                    'with' => array(
                                        'attribs' => array(
                                            'alias' => 'attribs',
                                            'order' => 'attribs.id ASC'
                                        )
                                    )
                                )
                            )
                        ));
                $item = MMItem::model()->find($criteria);
                if ($item) {
                    $this->renderPartial('//front/_itemdetails', array(
                        'item' => $item,
                        'item_key' => $item_key,
                        'order' => $order,
                    ));
                } else {
                    echo 'Item was not found.';
                }
            }

            elseif (isset($_POST['reorder'])) {
                $orders = NULL;
                if (!Yii::app()->user->isGuest){
                    $id = Yii::app()->user->id;
                    $orders = MMOrder::model()->findAll('customer_id = "'.$id.'" ORDER BY time_ordered DESC LIMIT 5');
                }
                $this->renderPartial('//front/_reorder',array('orders'=>$orders));
            }

            elseif (isset($_POST['additem'])) {
                $order = Yii::app()->session['wpmm_order'];
                $original = Yii::app()->session['wpmm_order'];
                if (empty($order))
                    $order = array();
                if (is_array($order['items'])) {
                    foreach ($order['items'] as $key => $order_item) {
                        if ($order_item['id'] == (int) $_POST['additem']['id'])
                            $sameIdKeys[] = $key;
                    }
                    if ($sameIdKeys) {
                        foreach ($sameIdKeys as $sameIdKey) {
                            if ($order['items'][$sameIdKey]['price'] == (float) $_POST['additem']['price'] &&
                                    $order['items'][$sameIdKey]['instructions'] == trim(strip_tags($_POST['additem']['instructions'])) &&
                                    $order['items'][$sameIdKey]['attributes'] == Helpers::arrayToFloat($_POST['additem']['attr'])
                            ) {
                                $order['items'][$sameIdKey]['quantity'] += (int) $_POST['additem']['quantity'];
                                $same_item = true;
                                break;
                            }
                        }
                    }
                    if (!$same_item) {
                        $order = Helpers::addSessionItem($order, $_POST['additem']);
                    }
                }
                else {
                    $order = Helpers::addSessionItem($order, $_POST['additem']);
                }
                Yii::app()->session['wpmm_order'] = $order;
                if ($order === $original)
                    exit(Helpers::json_encodealt(false));
                else
                    exit(Helpers::json_encodealt(true));
            }

            elseif (isset($_POST['savetip'])) {
                $order = Helpers::getSessionOrder();
                $order['cart_form']['payment-tip-type'] = trim(strip_tags($_POST['savetip']['type']));
                $order['cart_form']['payment-tip-value'] = (float)$_POST['savetip']['value'];
                Helpers::setSessionOrder($order);
                exit(Helpers::json_encodealt(is_numeric($order['cart_form']['payment-tip-value'])));
            }

            elseif (isset($_POST['saveordertime'])) {
                $order = Helpers::getSessionOrder();
                $order['cart_form']['order-time'] = strip_tags((trim($_POST['saveordertime']['datetime'])));
                Helpers::setSessionOrder($order);
                exit(Helpers::json_encodealt(!is_null($order['cart_form']['order-time'])));
            }

            elseif (isset($_POST['workinghours'])) {

            }

            elseif (isset($_POST['edititem'])) {
                $order = Helpers::getSessionOrder();
                $order_original = $order;
                $order['items'][(int) $_POST['edititem']['key']]['attributes'] = array();
                $order['items'][(int) $_POST['edititem']['key']]['instructions'] = null;
                $order['items'][(int) $_POST['edititem']['key']]['quantity'] = 1;
                if (count($_POST['edititem']['attr'])) {
                    $_POST['edititem']['attr'] = Helpers::arrayToFloat($_POST['edititem']['attr']);
                    foreach ($_POST['edititem']['attr'] as $attr_id => $attr_price) {
                        $order['items'][(int) $_POST['edititem']['key']]['attributes'][(int) $attr_id] = (float) $attr_price;
                    }
                }
                $order['items'][(int) $_POST['edititem']['key']]['instructions'] = trim(strip_tags($_POST['edititem']['instructions']));
                $order['items'][(int) $_POST['edititem']['key']]['quantity'] = (int) $_POST['edititem']['quantity'];
                Helpers::setSessionOrder($order);
                if ($order === $order_original)
                    exit(Helpers::json_encodealt(false));
                else
                    exit(Helpers::json_encodealt(true));
            }


            elseif (isset($_POST['deleteitem'])) {
                exit(Helpers::json_encodealt(Helpers::deleteSessionItem((int) $_POST['deleteitem'])));
            }

            elseif (isset($_POST['deleteorder'])) {
                Yii::app()->session['wpmm_order'] = array();
                exit(Helpers::json_encodealt(empty(Yii::app()->session['wpmm_order'])));
            }

            elseif (isset($_POST['refreshorder'])) {
                $order = Yii::app()->session['wpmm_order'];
                $order['newuserform']['name'] = trim(strip_tags($_POST['data']['cust_name']));
                $order['newuserform']['surname'] = trim(strip_tags($_POST['data']['cust_surname']));
                $order['newuserform']['c_mail'] = trim(strip_tags($_POST['data']['cust_c_mail']));
                $order['newuserform']['phone'] = trim(strip_tags($_POST['data']['cust_phone']));
                $order['cart_form']['payment_type'] = trim(strip_tags($_POST['data']['payment-type']));
                $order['cart_form']['delivery-type'] = trim(strip_tags($_POST['data']['delivery-type']));
                $order['cart_form']['payment-tip-value'] = trim(strip_tags($_POST['data']['payment-tip-value']));
                $order['cart_form']['payment-tip-type'] = trim(strip_tags($_POST['data']['payment-tip-type']));
                $order['cart_form']['delivery-notes'] = trim(strip_tags($_POST['data']['delivery-notes']));
                $order['cart_form']['promo_code'] = trim(strip_tags($_POST['data']['promo_code']));
                if($order['cart_form']['address'] == 'delete'){
                    $order['cart_form']['address'] = '';
                } else if (strlen(strpos($order['cart_form']['address'], 'wppp')) > 0) {
                    $order['cart_form']['address'] = substr($order['cart_form']['address'], 4);
                }else {
                    $order['cart_form']['address'] = trim(strip_tags($_POST['data']['address']));
                }
                $order['cart_form']['delivery-time'] = trim(strip_tags($_POST['data']['delivery-time']));
                Yii::app()->session['wpmm_order'] = $order;
                Yii::app()->controller->renderPartial('//front/_form');
                Yii::app()->end();
            }

            elseif (isset($_POST['checkout'])) {
                $order = Yii::app()->session['wpmm_order'];
                $erros = $order['errors'];

                $failed_order = false;
                $send_register_message = false;

                $addresses_to_save = array();

                $payment_stat = 0;
                $a_trans_id = '';

                $user = new MMCustomer();
                $currentorder = new MMOrder();
                $rand_user_pass = Helpers::randomPassword();

                if ($order['cart_form']['delivery-type'] == 'Pickup') {
                    $order['cart_form']['address'] = 0;
                }

                if ($order['cart_form']['payment_type'] == 'credit_card') {
                    if (isset($_POST['card_form'])) {
                        $order['card_form']['card_number'] = trim(strip_tags($_POST['card_form']['card_number']));
                        $order['card_form']['exp_date'] = trim(strip_tags($_POST['card_form']['exp_date']));
                        $order['card_form']['orderamount'] = trim(strip_tags($_POST['card_form']['orderamount']));
                        $order['card_form']['payment_vendor'] = trim(strip_tags($_POST['card_form']['payment_vendor']));
                        $order['card_form']['p_first_name'] = trim(strip_tags($_POST['card_form']['p_first_name']));
                        $order['card_form']['p_last_name'] = trim(strip_tags($_POST['card_form']['p_last_name']));
                        $order['card_form']['p_billing_address'] = trim(strip_tags($_POST['card_form']['p_billing_address']));
                        $order['card_form']['p_billing_country'] = trim(strip_tags($_POST['card_form']['p_billing_country']));
                        $order['card_form']['p_billing_city'] = trim(strip_tags($_POST['card_form']['p_billing_city']));
                        $order['card_form']['p_billing_zip'] = trim(strip_tags($_POST['card_form']['p_billing_zip']));
                        $order['card_form']['p_billing_state'] = trim(strip_tags($_POST['card_form']['p_billing_state']));
                        $order['card_form']['p_card_number'] = trim(strip_tags($_POST['card_form']['p_card_number']));
                        $order['card_form']['p_expiration_month'] = trim(strip_tags($_POST['card_form']['p_expiration_month']));
                        $order['card_form']['p_expiration_year'] = trim(strip_tags($_POST['card_form']['p_expiration_year']));
                        $order['card_form']['p_cv_code'] = trim(strip_tags($_POST['card_form']['p_cv_code']));
                        $order['card_form']['p_credit_type'] = trim(strip_tags($_POST['card_form']['p_credit_type']));
                        if(!empty($order['card_form']['payment_vendor'])){
                            if($order['card_form']['payment_vendor'] == 'Authorize.net'){
                                $trans_api_login = trim(MMSettingsForm::getParam('api_login_id'));
                                $trans_api_key = trim(MMSettingsForm::getParam('transaction_key'));
                                YiiBase::import('ext.anet_php_sdk.AuthorizeNet', true);
                                $a_transaction = new AuthorizeNetAIM($trans_api_login, $trans_api_key);
                                $a_transaction->amount = $order['card_form']['orderamount'];
                                $a_transaction->card_num = $order['card_form']['card_number'];
                                $a_transaction->exp_date = $order['card_form']['exp_date'];

                                $a_response = $a_transaction->authorizeAndCapture();

                                if ($a_response->approved) {
                                    $payment_stat = 1;
                                    $a_trans_id = $a_response->transaction_id;
                                } else {
                                    $erros['card_form'] = $a_response->response_reason_text;
                                    $failed_order = true;
                                }
                            } else if($order['card_form']['payment_vendor'] == 'PayPal'){
                                Yii::app()->Paypal->apiUsername=trim(MMSettingsForm::getParam('apiUsername'));
                                Yii::app()->Paypal->apiPassword=trim(MMSettingsForm::getParam('apiPassword'));
                                Yii::app()->Paypal->apiSignature=trim(MMSettingsForm::getParam('apiSignature'));
                                Yii::app()->Paypal->init();
                                $paymentInfo = array(
                                    'Member'=> array(
                                        'first_name'=>$order['card_form']['p_first_name'],
                                        'last_name'=>$order['card_form']['p_last_name'],
                                        'billing_address'=>$order['card_form']['p_billing_address'],
                                        'billing_address2'=>'',
                                        'billing_country'=>$order['card_form']['p_billing_country'],
                                        'billing_city'=>$order['card_form']['p_billing_city'],
                                        'billing_state'=>$order['card_form']['p_billing_state'],
                                        'billing_zip'=>$order['card_form']['p_billing_zip']
                                    ),
                                    'CreditCard'=>
                                    array(
                                        'card_number'=>$order['card_form']['p_card_number'],
                                        'expiration_month'=>$order['card_form']['p_expiration_month'],
                                        'expiration_year'=>$order['card_form']['p_expiration_year'],
                                        'cv_code'=>$order['card_form']['p_cv_code'],
                                        'credit_type'=>$order['card_form']['p_credit_type']
                                    ),
                                    'Order'=> array('theTotal'=>$order['card_form']['orderamount'])
                                );
                                $result = Yii::app()->Paypal->DoDirectPayment($paymentInfo);
                                if(!Yii::app()->Paypal->isCallSucceeded($result)){
                                    if(Yii::app()->Paypal->apiLive === true){
                                        //Live mode basic error message
                                        $erros['card_form'] = $result['L_LONGMESSAGE0'];
                                        $failed_order = true;
                                    }else{
                                        //Sandbox output the actual error message to dive in.
                                        $erros['card_form'] = $result['L_LONGMESSAGE0'];
                                        $failed_order = true;
                                    }
                                    if($result['Error']){
                                        $erros['card_form'] = 'couldn\'t connect to host';
                                    }
                                } else {
                                   $payment_stat = 1;
                                   $a_trans_id = 00000;
                                }
                            }
                        } else {
                           $erros['card_form'] = 'Please select payment proccesor!';
                           $failed_order = true;
                        }
                    }
                } else {
                    if (isset($order['card_form']))
                        unset($order['card_form']);
                }

                if ($failed_order == false) {
                    $trans = Yii::app()->db->beginTransaction();
                    try {
                        if ($failed_order == false) {
                            if (Yii::app()->user->isGuest) {
                                $user->attributes = $order['newuserform'];
                                $user->password = $rand_user_pass;
                                if ($user->save()) {
                                    $send_register_message = true;
                                } else {
                                    $erros['user'][] = 'User profile cannot be saved.';
                                    $failed_order = true;
                                }

                                if (isset($order['address'])) {
                                    $isadrrs = false;
                                    foreach ($order['address'] as $adrr) {
                                        if ($order['cart_form']['address'] == $adrr['id']) {
                                            $order['cart_form']['address'] = $adrr['id'];
                                            $isadrrs = true;
                                            if (!Helpers::ValidateZipTable($adrr['location'])) {
                                                $erros['delivery'][] = 'Invalid zipcode provided.';
                                                $failed_order = true;
                                            }
                                        }
                                        $adresmodel = new MMAddress();
                                        $adresmodel->city = $adrr['city'];
                                        $adresmodel->state = $adrr['state'];
                                        $adresmodel->address = $adrr['address'];
                                        $adresmodel->location = $adrr['location'];
                                        $addresses_to_save [] = $adresmodel;
                                    }
                                    if ($isadrrs == false && $order['cart_form']['address'] != 0) {
                                        $erros['delivery'][] = 'Please select the valid address';
                                        $failed_order = true;
                                    }
                                }

                                if (!empty($user->id) && $failed_order == false) {
                                    foreach ($addresses_to_save as $newaddr) {
                                        $afteradrrsave = true;
                                        if ($order['cart_form']['address'] == $newaddr->id) {
                                            $afteradrrsave = true;
                                        }
                                        $newaddr->customer_id = $user->id;
                                        $newaddr->save(false);
                                        if ($afteradrrsave == true)
                                            $order['cart_form']['address'] = $newaddr->id;
                                    }
                                }
                            } else {
                                $user = MMCustomer::model()->findByPk(Yii::app()->user->id);
                                if ($user == NULL) {
                                    $erros['user'][] = 'Unknown user';
                                    $failed_order = true;
                                }
                            }
                        }
                        if ($failed_order == false) {
                            $order_params = array(
                                'tax' => MMSettingsForm::getParam('tax_rate'),
                                'discount' => MMSettingsForm::getParam('discount'),
                                'delivery' => MMSettingsForm::getParam('delivery_charge'),
                                'timezone' => MMSettingsForm::getParam('timezone'),
                                'reorder' => '1'
                            );
                            $promocode_params = array();
                             if (!empty($order['cart_form']['promo_code'])) {
                                $code =  MMCoupons::model()->find('code ="'.$order['cart_form']['promo_code'].'"');
                                $promocode_params = array(
                                    'code' => $code->code,
                                    'amount' => $code->amount,
                                    'type' => $code->type,
                                );
                            }
                            $currentorder->time_ordered = date('Y-m-d H:i:s');
                            $currentorder->time_delivery = date('Y-m-d H:i:s', strtotime($order['cart_form']['order-time']));
                            $currentorder->notes = $order['cart_form']['delivery-notes'];
                            $currentorder->payment_type = $order['cart_form']['payment_type'];
                            $currentorder->payment_status = 'ordered';
                            $currentorder->payment = $payment_stat;
                            $currentorder->delivery_type = $order['cart_form']['delivery-type'];
                            $currentorder->tip_type = $order['cart_form']['payment-tip-type'];
                            $currentorder->tip = $order['cart_form']['payment-tip-value'];
                            $currentorder->address_id = $order['cart_form']['address'];
                            $currentorder->customer_id = $user->id;
                            $currentorder->trans_id = $a_trans_id;
                            $currentorder->params = serialize($order_params);
                            $currentorder->promo_code = serialize($promocode_params);
                            if ($currentorder->save()) {
                                foreach ($order['items'] as $order_item) {
                                    $new_order_item = new MMOrderItem();
                                    $item_tmp = Helpers::getItemById($order_item['id']);
                                    $attribs = array();
                                    $new_order_item->order_id = $currentorder->id;
                                    $new_order_item->itemid = $item_tmp->id;
                                    $new_order_item->item_name = $item_tmp->name;
                                    $new_order_item->item_price = (float) $item_tmp->price;
                                    $new_order_item->instructions = $order_item['instructions'];
                                    $attribs['quantity'] = (int) $order_item['quantity'];
                                    if (count($order_item['attributes'])) {
                                        foreach ($order_item['attributes'] as $attr_id => $attr_price) {
                                            $attr = Helpers::getAttrById($attr_id);
                                            $attribs[] = array('orig_id'=>$attr->id, 'name' => $attr->name, 'price' => (float) $attr->price);
                                        }
                                    }
                                    $new_order_item->attribs = serialize($attribs);
                                    if (!$new_order_item->save())
                                        $failed_order = true;
                                }
                            } else {
                                $failed_order = true;
                            }
                        }
                        $trans->commit();
                    } catch (Exception $e) {
                        $trans->rollback();
                        $failed_order = true;
                        $erros['items'][] = 'General DB error.';
                    }
                }
                if ($failed_order == false) {
                    YiiBase::import('ext.YiiMail.YiiMailMessage');
                    $shopemail = MMSettingsForm::getParam('vendor_email');
                    $shopname = MMSettingsForm::getParam('vendor_name');
                    $enable_fax = MMSettingsForm::getParam('enable_efax');
                    $enable_gcp = MMSettingsForm::getParam('enable_gcp');
                    if ($send_register_message == true) {
                        Helpers::sendRegisterMessage($user, $rand_user_pass);
                    }
                    if ($enable_fax) {
                        $fax_number = MMSettingsForm::getParam('vendor_fax');
                        $login_id = MMSettingsForm::getParam('efax_login_id');
                        $login_name = MMSettingsForm::getParam('efax_username');
                        $login_pass = MMSettingsForm::getParam('efax_password');
                        YiiBase::import('application.extensions.efax.*');
                        $fax = new EFax();
                        $fax->setParams($login_name, $login_pass, $login_id, $fax_number, $shopname);
                        $fax->sendFax(Yii::app()->controller->renderPartial('//admin/_orderblank', array('model' => $currentorder, 'full' => true), true));
                    }
                    if ($enable_gcp) {
                        $gcp_login = MMSettingsForm::getParam('gcp_email');
                        $gcp_pass = MMSettingsForm::getParam('gcp_pass');
                        if(!empty($gcp_login) && !empty($gcp_pass)){
                            $gcp = new MMGCP();
                            if($gcp->loginToGoogle($gcp_login, $gcp_pass)) {
                                $printerid = MMSettingsForm::getParam('gcp_printer_id');
                                $gcp->sendPrintToPrinter($printerid, "Printing WpMenuMaker Order#".$currentorder->id, Yii::app()->controller->renderPartial('//admin/_orderblank', array('model' => $currentorder, 'full' => true), true), "text/html");
                            }
                        }
                    }
                    Helpers::sendOrderToEmail($currentorder, $user->c_mail, false);
                    Helpers::sendOrderToEmail($currentorder, $shopemail, true);
                    unset(Yii::app()->session['wpmm_order']);
                    Yii::app()->controller->renderPartial('//front/_ordercomplete', array('order' => $currentorder));
                } else {
                    $order['errors'] = $erros;
                    Yii::app()->session['wpmm_order'] = $order;
                    if ($order['cart_form']['payment_type'] == 'credit_card') {
                        Yii::app()->controller->renderPartial('//front/_cardform');
                    } else {
                        echo 'There where errors';
                    }
                }
                Yii::app()->end();
            }
        } elseif (empty(Yii::app()->session->sessionId)) {
            echo "Your session has expired. Please refresh the page.";
        } else {
            echo '<p align="center">CSRF verification failed</p>';
        }

        Yii::app()->end();
    }

}

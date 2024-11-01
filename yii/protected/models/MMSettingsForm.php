<?php
class MMSettingsForm extends CFormModel {

    public $license_key;
    public $theme;
    public $layout;
    public $zip_self;
    public $zip_list;
    public $vendor_name;
    public $vendor_email;
    public $vendor_city;
    public $vendor_state;
    public $vendor_street;
    public $vendor_phone;
    public $vendor_fax;
    public $zip_type;
    public $zipcode;
    public $tax_rate;
    public $delivery_charge;
    public $discount;
    public $min_order;
    public $item_sort_field;
    public $item_sort_order;
    public $cart_position;
    public $file;
    public $textfile;
    public $tabs_effect;
    public $items_pict;
    public $cat_pict;
    public $cat_sort_field;
    public $cat_sort_order;
    public $work_time;
    public $timezone;
    public $api_login_id;
    public $transaction_key;
    public $enable_payments;
    public $efax_login_id;
    public $efax_username;
    public $efax_password;
    public $enable_efax;
    public $powered_by;
    public $apiUsername;
    public $apiPassword;
    public $apiSignature;
    public $enable_payments_paypal;
    public $enable_delivery;
    public $item_mode;
    public $enable_social;
    public $enable_gcp;
    public $gcp_email;
    public $gcp_pass;
    public $gcp_printer_id;

    public function attributeLabels() {
        return array(
            'license_key' => 'License key',
            'theme' => 'Plugin theme',
            'layout' => 'Menus layout',
            'powered_by' => 'Show Powered By link',
            'zip_type' => 'ZIP code verefication mode',
            'zip_self' => 'Restaurant ZIP code',
            'zip_list' => 'Allowed ZIP codes',
            'zipcode' => 'ZIP code',
            'vendor_name' => 'Restaurant name',
            'vendor_email' => 'Restaurant e-mail',
            'vendor_city' => 'Restaurant city',
            'vendor_state' => 'Restaurant state',
            'vendor_street' => 'Restaurant street address (255 chars)',
            'vendor_phone' => 'Restaurant phone',
            'vendor_fax' => 'Restaurant fax',
            'tax_rate' => 'Tax rate (%)',
            'delivery_charge' => 'Delivery charge ($)',
            'discount' => 'Discount (%)',
            'min_order' => 'Minimum order amount ($)',
            'item_sort_field' => 'Sort items by',
            'item_sort_order' => 'Sort order',
            'cart_position' => 'Shopping cart position',
            'tabs_effect' => 'Tabs transition effect',
            'items_pict'=>'Enable Items Pictures',
            'cat_pict'=>'Enable Category Pictures',
            'cat_sort_field'=>'Sort categories by',
            'cat_sort_order'=>'Sort order',
            'timezone'=>'Timezone:',
            'api_login_id'=>'API Login ID',
            'transaction_key'=>'Transaction Key',
            'enable_payments' => 'Authorize.net payments',
            'efax_login_id'=>'Company',
            'efax_username'=>'Username',
            'efax_password'=>'Password',
            'enable_efax'=>'Enable FAXAGE',
            'app_name'=>'Application title',
            'apiUsername'=>'Paypal API username',
            'apiPassword'=>'API Password',
            'apiSignature'=>'API Signature',
            'enable_payments_paypal'=>'Enable PayPal',
            'enable_delivery'=>'Enable Delivery',
            'item_mode'=>'Item Display style',
            'enable_social'=>'Enable social buttons',
            'enable_gcp' => 'Enable Google Cloud Print',
            'gcp_email' => 'Google Account email',
            'gcp_pass' => 'Google Account password',
            'gcp_printer_id' => 'Google Cloud Print printer id',
        );
    }

    public function rules() {
        return array(
            array('zip_self',
                'zipcodevalidatebug'),
            array('vendor_email',
                'email', 'message' => 'Invalid Email address'),
            array('tax_rate, delivery_charge, discount, min_order',
                'numerical'),
            array('gcp_printer_id, gcp_pass, gcp_email, enable_gcp, enable_social, item_mode, enable_delivery, enable_payments_paypal ,apiSignature ,apiPassword ,apiUsername, enable_efax, efax_password, efax_username, efax_login_id, license_key, theme, layout, powered_by, zip_type, zip_list, vendor_name, vendor_city, vendor_state, vendor_street, vendor_phone, vendor_fax, item_sort_field, item_sort_order, cart_position, file, textfile, zipcode, tabs_effect, items_pict, cat_pict, cat_sort_field, cat_sort_order, work_time, timezone, api_login_id, transaction_key, enable_payments',
                'safe'),
            array('zipcode',
                'zipcodevalidate', 'on' => 'addzipcode'),
            array('file',
                'file', 'allowEmpty' => true, 'on' => 'installtheme'),
        );
    }

    public function zipcodevalidate() {
        if (!preg_match("/(^[ABCEGHJKLMNPRSTVXY]\d[ABCEGHJKLMNPRSTVWXYZ]( )?\d[ABCEGHJKLMNPRSTVWXYZ]\d$)|(^\d{5}(-\d{4})?$)/", $this->zipcode))
        $this->addError('zipcode', 'Incorrect ZIP code format');
    }

    public function zipcodevalidatebug() {
        if (!preg_match("/(^[ABCEGHJKLMNPRSTVXY]\d[ABCEGHJKLMNPRSTVWXYZ]( )?\d[ABCEGHJKLMNPRSTVWXYZ]\d$)|(^\d{5}(-\d{4})?$)/", $this->zip_self))
        $this->addError('zip_self', 'Incorrect ZIP code format');
    }

    public function getThemesOptions() {
        $path = realpath(YiiBase::getPathOfAlias('application') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'themes');
        $dir = opendir($path);
        while (false !== ($name = readdir($dir))) {
            if ($name == '.' || $name == '..')
                continue;
            $file = $path . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . $name . '.xml';
            if (file_exists($file)) {
                $theme = new SimpleXMLElement(file_get_contents($file));
                $themes[$name] = array('fullname' => $theme->fullname,
                    'author' => $theme->author,
                    'date' => $theme->date,
                    'version' => $theme->version,
                    'name' => $name,
                    'thumb' => MM_URL . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . "thumb.png",
                );
            }
        }
        closedir($dir);
        return $themes;
    }

    public function getJqueryOptions() {
        if (self::getParam('jquery') === '1') {
            $options['checked'] = 'checked';
        }
        return $options;
    }

    static function getParam($param) {
        return Yii::app()->db->createCommand()
                        ->select('value')
                        ->from(Wpmm::getTableNames('settings'))
                        ->where("name = '$param'")
                        ->queryScalar();
    }

    static function getParams() {
        $params_multiarray = Yii::app()->db->createCommand()
                        ->select('name, value')
                        ->from(Wpmm::getTableNames('settings'))
                        ->queryAll();
        foreach ($params_multiarray as $param) {
            $params[$param['name']] = $param['value'];
        }
        return $params;
    }

    public function save($param = null) {
        $sql = "UPDATE `" . Wpmm::getTableNames('settings') . "` SET `value`=:value WHERE `name`=:name";
        $command = Yii::app()->db->createCommand($sql);
        if (is_null($param)) {
            foreach ($this->attributes as $name => $value) {
                if ($name == 'zipcode')
                    continue;
                if ($this->validate(array($name))) {
                $command->bindParam(':value', $value, PDO::PARAM_STR)
                        ->bindParam(':name', $name, PDO::PARAM_STR)
                        ->execute();
                }
            }
        }
        elseif (in_array($param, array_keys(Wpmm::getDefaultSettings()))) {
            $command->bindParam(':value', $this->{$param}, PDO::PARAM_STR)
                    ->bindParam(':name', $param, PDO::PARAM_STR)
                    ->execute();
        }
        else {
            return false;
        }
        return true;
    }
}
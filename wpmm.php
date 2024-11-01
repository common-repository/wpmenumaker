<?php
/*
  Plugin Name: WPMenuMaker
  Plugin URI: http://wpmenumaker.com
  Description: Restaurant menu management plugin for restaurant owners and catering agencies, with commercial extension for online ordering. Available features: create unlimited menus and items, customers and orders management, pick-up and delivery options, delivery time and more. Receive orders via Fax or Email. Customers can order food directly on your website with extended version of the plugin.
  Version: 1.1.2
  Author: Webika Design
  Author URI: http://www.webika.com
 */
require_once 'wpmm-config.php';
class Wpmm {

    public $db_version = '0.18';
    public $app;

    public function __construct() {
        error_reporting(E_ERROR);
        if(!is_admin()) $this->checkPermissions(true);

         if($this->checkPermissions()){
            if (defined('YII_PATH')) {
                wp_die('<h3 align="center">Cannot activate WPMenuMaker!</h3><h4 align="center">Another Yii application is already running.</h4><p align="center"><a href="#" onclick="window.location=document.referrer">Go back</a></p>');
            } else {
                defined('YII_DEBUG') or define('YII_DEBUG', true);
                defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);
                require_once MM_YII_PATH . 'framework' . DIRECTORY_SEPARATOR . 'yii.php';
            }
            $config = MM_YII_PATH . 'protected' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'main.php';
            $this->app = Yii::createWebApplication($config);
            Yii::app()->session->close();
            spl_autoload_unregister(array('YiiBase', 'autoload'));
        }
        if (is_admin()) {
            register_activation_hook(__FILE__, array($this, 'install'));
            add_action('admin_menu', array($this, 'menu'));
            add_action('admin_enqueue_scripts', array($this, 'renderCss'), 10000);
            add_action('admin_enqueue_scripts', array($this, 'renderJs'), 10000);
            add_action('wp_ajax_admin', array($this, 'ajaxAdmin'));

            add_action('wp_ajax_relationalorder', array($this,'ajaxRelationalOrder'));

            add_action('wp_ajax_savestatus', array($this,'ajaxSaveStatus'));

            add_action('wp_ajax_front', array($this, 'ajaxFront'));
            add_action('wp_ajax_nopriv_front', array($this, 'ajaxFront'));
        } else {
            add_shortcode('wpmm', array($this, 'shortcodeRender'));
            add_action('wp_enqueue_scripts', array($this, 'renderCss'), 10000);
            add_action('wp_enqueue_scripts', array($this, 'renderJs'), 10000);
        }
    }

    public function closeSession() {
        $this->runYii(array($this->app, 'getSession'))->close();
    }

    public function ajaxAdmin() {
        $this->runYii(array($this->app, 'runController'), array('ajax/admin'));
    }

    public function ajaxFront() {
        $this->runYii(array($this->app, 'runController'), array('ajax/front'));
    }

    public function ajaxRelationalOrder() {
        $this->runYii(array($this->app, 'runController'), array('admin/relationalorder'));
    }

    public function ajaxSaveStatus() {
        $this->runYii(array($this->app, 'runController'), array('admin/savestatus'));
    }

    public function getMenuPages() {
        return array(
            'Orders' => 'wpmm_orders',
            'Menus' => 'wpmm_menus',
            'Categories' => 'wpmm_categories',
            'Items' => 'wpmm_items',
            'Customers' => 'wpmm_customers',
            'Settings' => 'wpmm_settings',
        );
    }

    public function getFrontendCss() {
        return array(
            'wpmm-mobiscroll' => 'css/mobiscroll-2.3.1.custom.min.css',
            'wpmm-lightbox' => 'css/jquery.fancybox-1.3.4.css',
            'wpmm-theme' => 'themes/' . $this->runYii(array('MMSettingsForm', 'getParam'), array('theme')) . '/css/theme.css' ,
            'wpmm-front' => 'css/wpmm-front.css',
        );
    }

    public function getFrontendJs() {
        wp_enqueue_script('jquery');
        $wp_jquery_ver = $GLOBALS['wp_scripts']->registered['jquery']->ver;
        if (empty($wp_jquery_ver))
            $wp_jquery_ver = '1.9.2';
        return array(
            'wpmm-jquery-ui' => 'http://code.jquery.com/ui/1.10.1/jquery-ui.min.js',
            'wpmm-jquery-mousewheel' => 'js/jquery.mousewheel.js',
            'wpmm-jquery-jscrollbar' => 'js/jquery.mCustomScrollbar.min.js',
            'wpmm-jquery-timepicker' => 'js/jquery.timepicker.js',
            'wpmm-jquery-mobiscroll' => 'js/mobiscroll-2.3.1.custom.min.js',
            'wpmm-jquery-fancybox' => 'js/jquery.fancybox-1.3.4.pack.js',
        );
    }

    public function getAdminCss() {
        return array(
            'wpmm-bootstrap' => 'css/wpmm-bootstrap.css',
            'wpmm-bootstrap-yii' => 'css/bootstrap-yii.css',
            'wpmm-bootstrap-box' => 'css/bootstrap-box.css',
            'wpmm-admin' => 'css/wpmm-admin.css',
        );
    }

    public function getAdminJs() {
        return array(

        );
    }

    public function renderCss() {
        if (is_admin())
            $css = $this->getAdminCss();
        else
            $css = $this->getFrontendCss();
        foreach ($css as $name => $path) {
            if (file_exists(MM_PATH . $path)) {
                wp_register_style($name, MM_URL . $path);
                wp_enqueue_style($name);
            }
            elseif ( substr($path, 0, 7) == 'http://' ) {
                wp_register_style($name, $path);
                wp_enqueue_style($name);
            }
        }
    }

    public function renderJs() {
        if (is_admin()){
            $js = $this->getAdminJs();
        }
        else {
            $js = $this->getFrontendJs();
        }
        foreach ($js as $name => $path) {
            if (file_exists(MM_YII_PATH . $path)) {
                wp_register_script($name, MM_YII_URL . $path);
                wp_enqueue_script($name);
            }
            elseif ( substr($path, 0, 7) == 'http://' ) {
                wp_register_script($name, $path);
                wp_enqueue_script($name);
            }
        }
    }

    public function shortcodeRender($attributes) {
        if (isset($attributes['id'])) {
            $this->app->params['menu'] = (int) $attributes['id'];
        }
        global $wpmm_count;
        if (!$wpmm_count) {
            $wpmm_count++;
            return $this->runYii(array($this->app, 'runController'), array('front/index'));
        }
    }

    public function install() {
        global $wpdb;
        $sql = array(
            'menus' => "CREATE TABLE `" . self::getTableNames('menus') . "` (
                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
                        `name` VARCHAR(255) NOT NULL ,
                        `description` TEXT NOT NULL ,
                        `image` VARCHAR(255) NOT NULL ,
                        `time_from` TINYINT NOT NULL ,
                        `time_to` TINYINT NOT NULL ,
                        `sort_order` INT UNSIGNED NOT NULL ,
                        `published` TINYINT UNSIGNED NOT NULL DEFAULT '1',
                        PRIMARY KEY  (`id`) )
                        ENGINE = INNODB
                        CHARACTER SET " . MM_DB_CHARSET . "
                        COLLATE " . MM_DB_COLLATE,
            'categories' => "CREATE TABLE `" . self::getTableNames('categories') . "` (
                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
                        `name` VARCHAR(255) NOT NULL ,
                        `description` TEXT NOT NULL ,
                        `image` VARCHAR(255) NOT NULL ,
                        `published` TINYINT UNSIGNED NOT NULL DEFAULT '1',
                        PRIMARY KEY  (`id`) )
                        ENGINE = INNODB
                        CHARACTER SET " . MM_DB_CHARSET . "
                        COLLATE " . MM_DB_COLLATE,
            'items' => "CREATE TABLE `" . self::getTableNames('items') . "` (
                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
                        `name` VARCHAR(255) NOT NULL ,
                        `description` TEXT NOT NULL ,
                        `image` VARCHAR(255) NOT NULL ,
                        `size` VARCHAR(45) NOT NULL ,
                        `price` DECIMAL(10,2) NOT NULL ,
                        `published` TINYINT UNSIGNED NOT NULL DEFAULT '1',
                        PRIMARY KEY  (`id`) )
                        ENGINE = INNODB
                        CHARACTER SET " . MM_DB_CHARSET . "
                        COLLATE " . MM_DB_COLLATE,
            'addresses' => "CREATE TABLE `" . self::getTableNames('addresses') . "` (
                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
                        `city` VARCHAR(255) NOT NULL ,
                        `state` CHAR(2) NOT NULL ,
                        `address` VARCHAR(255) NOT NULL ,
                        `location` VARCHAR(255) NOT NULL ,
                        `order_id` INT UNSIGNED NOT NULL ,
                        `customer_id` INT UNSIGNED NOT NULL ,
                        PRIMARY KEY  (`id`) )
                        ENGINE = INNODB
                        CHARACTER SET " . MM_DB_CHARSET . "
                        COLLATE " . MM_DB_COLLATE,
            'menu_categories' => "CREATE TABLE `" . self::getTableNames('menu_categories') . "` (
                        `menu_id` INT UNSIGNED NOT NULL ,
                        `category_id` INT UNSIGNED NOT NULL ,
                        PRIMARY KEY  (`menu_id`, `category_id`) )
                        ENGINE = INNODB
                        CHARACTER SET " . MM_DB_CHARSET . "
                        COLLATE " . MM_DB_COLLATE,
            'category_items' => "CREATE TABLE `" . self::getTableNames('category_items') . "` (
                        `category_id` INT UNSIGNED NOT NULL ,
                        `item_id` INT UNSIGNED NOT NULL ,
                        PRIMARY KEY  (`category_id`, `item_id`) )
                        ENGINE = INNODB
                        CHARACTER SET " . MM_DB_CHARSET . "
                        COLLATE " . MM_DB_COLLATE,
            'groups' => "CREATE TABLE `" . self::getTableNames('groups') . "` (
                        `id` INT UNSIGNED NOT NULL auto_increment,
                        `name` varchar(255) NOT NULL,
                        `type` TINYINT NOT NULL,
                        `item_id` INT UNSIGNED NOT NULL,
                        PRIMARY KEY  (`id`)
                      ) ENGINE = InnoDB
                        CHARACTER SET " . MM_DB_CHARSET . "
                        COLLATE " . MM_DB_COLLATE,
            'attributes' => "CREATE TABLE `" . self::getTableNames('attributes') . "` (
                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
                        `name` VARCHAR(255) NOT NULL ,
                        `price` DECIMAL(10,2) NOT NULL ,
                        `checked_id` TINYINT UNSIGNED NOT NULL DEFAULT '0',
                        `group_id` INT UNSIGNED NOT NULL ,
                        PRIMARY KEY  (`id`) )
                        ENGINE = INNODB
                        CHARACTER SET " . MM_DB_CHARSET . "
                        COLLATE " . MM_DB_COLLATE,
            'orders' => "CREATE TABLE `" . self::getTableNames('orders') . "` (
                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
                        `time_ordered` TIMESTAMP NOT NULL ,
                        `time_delivery` TIMESTAMP NOT NULL ,
                        `payment_type` ENUM('cash','credit_card') NOT NULL ,
                        `payment_status` ENUM('ordered','completed','paused','canceled') NOT NULL DEFAULT 'ordered' ,
                        `payment` TINYINT UNSIGNED NOT NULL DEFAULT '0',
                        `delivery_type` ENUM('Delivery','Pickup') NOT NULL DEFAULT 'Delivery' ,
                        `tip_type` ENUM('percent','amount') NOT NULL DEFAULT 'percent' ,
                        `tip` DECIMAL(10,2) NOT NULL DEFAULT '0',
                        `promo_code` TEXT NOT NULL ,
                        `notes` TEXT NOT NULL ,
                        `params` TEXT NOT NULL ,
                        `trans_id` VARCHAR(255) NOT NULL ,
                        `address_id` INT UNSIGNED NOT NULL ,
                        `customer_id` INT UNSIGNED NOT NULL ,
                        PRIMARY KEY  (`id`) )
                        ENGINE = INNODB
                        CHARACTER SET " . MM_DB_CHARSET . "
                        COLLATE " . MM_DB_COLLATE,
             'coupons' => "CREATE TABLE `" . self::getTableNames('coupons') . "` (
                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
                        `name` VARCHAR(255) NOT NULL ,
                        `code` VARCHAR(255) NOT NULL ,
                        `amount` DECIMAL(10,2) NOT NULL DEFAULT '0' ,
                        `type` TINYINT UNSIGNED NOT NULL DEFAULT '0' ,
                        `cfrom` TIMESTAMP NOT NULL ,
                        `cto` TIMESTAMP NOT NULL ,
                        `active`  TINYINT UNSIGNED NOT NULL DEFAULT '0' ,
                        PRIMARY KEY  (`id`) )
                        ENGINE = INNODB
                        CHARACTER SET " . MM_DB_CHARSET . "
                        COLLATE " . MM_DB_COLLATE,
            'order_items' => "CREATE TABLE `" . self::getTableNames('order_items') . "` (
                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
                        `order_id` INT UNSIGNED NOT NULL ,
                        `itemid` INT UNSIGNED NOT NULL ,
                        `item_name` VARCHAR(255) NOT NULL ,
                        `item_price` DECIMAL(10,2) NOT NULL ,
                        `instructions` TEXT NOT NULL ,
                        `attribs` LONGTEXT NOT NULL ,
                        PRIMARY KEY  (`id`) )
                        ENGINE = INNODB
                        CHARACTER SET " . MM_DB_CHARSET . "
                        COLLATE " . MM_DB_COLLATE,
            'settings' => "CREATE TABLE `" . self::getTableNames('settings') . "` (
                        `name` VARCHAR(255) NOT NULL ,
                        `value` TEXT NOT NULL ,
                        PRIMARY KEY  (`name`) )
                        ENGINE = INNODB
                        CHARACTER SET " . MM_DB_CHARSET . "
                        COLLATE " . MM_DB_COLLATE,
            'customers' => "CREATE TABLE `" . self::getTableNames('customers') . "` (
                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
                        `c_mail` VARCHAR(255) NOT NULL ,
                        `name` VARCHAR(255) NOT NULL ,
                        `surname` VARCHAR(255) NOT NULL ,
                        `password` VARCHAR(255) NOT NULL ,
                        `salt` VARCHAR(255) NOT NULL ,
                        `phone` VARCHAR(255) NOT NULL ,
                        PRIMARY KEY  (`id`) )
                        ENGINE = INNODB
                        CHARACTER SET " . MM_DB_CHARSET . "
                        COLLATE " . MM_DB_COLLATE,
        );
        foreach (self::getTableNames() as $table => $name) {
            if ($wpdb->get_var("show tables like `$name`") != $name) {
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql[$table]);
            }
        }
        $tablename = self::getTableNames('settings');
        $default = self::getDefaultSettings();
        if (!get_option('wpmm_db_version')) {
            add_option('wpmm_db_version', $this->db_version);
            $last = key(array_reverse($default));
            $settings = "INSERT INTO `{$tablename}` (`name`, `value`) VALUES ";
            foreach ($default as $name => $value) {
                $settings .= "('$name','$value')" . ($name === $last ? '' : ',');
            }
            $wpdb->query($settings) or wp_die($wpdb->last_error);
        }
        $current_ver = get_option('wpmm_db_version');
        if ($current_ver != $this->db_version) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $query = "SELECT `name` FROM `{$tablename}`";
            $settings = $wpdb->get_col($query) or wp_die($wpdb->last_error);
            foreach (array_keys($default) as $param) {
                if (!in_array($param, $settings)) {
                    $wpdb->insert(
                            self::getTableNames('settings'),
                            array(
                                'name' => $param,
                                'value' => $default[$param]
                            )
                    ) or wp_die($wpdb->last_error);
                }
            }
            foreach (self::getTableNames() as $table => $name) {
                dbDelta($sql[$table]);
            }
            update_option('wpmm_db_version', $this->db_version);
        }
    }

    static function getDefaultSettings() {
        return array(
            'license_key' => '',
            'theme' => 'light',
            'layout' => 'vertical',
            'zip_type' => '2',
            'zip_self'=>'',
            'zip_list'=>'',
            'vendor_name'=>'',
            'vendor_email'=>'',
            'vendor_city'=>'',
            'vendor_state'=>'',
            'vendor_street'=>'',
            'vendor_phone'=>'',
            'vendor_fax'=>'',
            'tax_rate'=>'0',
            'delivery_charge'=>'0',
            'discount' => '0',
            'min_order' => '0',
            'item_sort_field'=>'id',
            'item_sort_order'=>'ASC',
            'cart_position'=>'inside-left',
            'cart_type'=>'1',
            'tabs_effect' => 'fade',
            'cat_pict'=>'1',
            'items_pict'=>'1',
            'cat_sort_order'=>'ASC',
            'cat_sort_field'=>'id',
            'work_time'=>'a:7:{i:0;a:3:{s:6:"active";s:1:"1";s:4:"from";s:1:"9";s:2:"to";s:2:"18";}i:1;a:3:{s:6:"active";s:1:"1";s:4:"from";s:1:"9";s:2:"to";s:2:"18";}i:2;a:3:{s:6:"active";s:1:"1";s:4:"from";s:1:"9";s:2:"to";s:2:"18";}i:3;a:3:{s:6:"active";s:1:"1";s:4:"from";s:1:"9";s:2:"to";s:2:"18";}i:4;a:3:{s:6:"active";s:1:"1";s:4:"from";s:1:"9";s:2:"to";s:2:"18";}i:5;a:3:{s:6:"active";s:1:"1";s:4:"from";s:1:"9";s:2:"to";s:2:"18";}i:6;a:3:{s:6:"active";s:1:"1";s:4:"from";s:1:"9";s:2:"to";s:2:"15";}}',
            'timezone'=>'America/New_York',
            'api_login_id'=>'******',
            'transaction_key'=>'******',
            'enable_payments' => '0',
            'enable_efax' => '0',
            'efax_login_id' => '',
            'efax_username' => '',
            'efax_password' => '',
            'powered_by' => '0',
            'enable_payments_paypal'=>'0',
            'apiSignature'=>'',
            'apiPassword'=>'',
            'apiUsername'=>'',
            'enable_delivery'=>'1',
            'item_mode'=>'0',
            'enable_social'=>'0',
            'enable_gcp' => '0',
            'gcp_email' => '',
            'gcp_pass' => '******',
            'gcp_printer_id' => '',
        );
    }

    static function getTableNames($table = NULL) {
        $tables = array(
            'addresses',
            'attributes',
            'categories',
            'category_items',
            'groups',
            'items',
            'menu_categories',
            'menus',
            'order_items',
            'orders',
            'portions',
            'settings',
            'customers',
            'coupons',
        );
        if (is_null($table)) {
            foreach ($tables as $table) {
                $names[$table] = MM_DB_PREFIX . MM_TABLE_PREFIX . $table;
            }
            return $names;
        } elseif (in_array($table, $tables)) {
            return MM_DB_PREFIX . MM_TABLE_PREFIX . $table;
        } else {
            return FALSE;
        }
    }

    public function setFtpPrem($url){
        clearstatcache();
        $assets = MM_YII_PATH . 'assets';
        $runtime = MM_YII_PATH . 'protected' . DIRECTORY_SEPARATOR . 'runtime';
        $uploads = MM_YII_PATH . 'images' . DIRECTORY_SEPARATOR . 'uploads';
        $themes = MM_PATH . 'themes';
        $uploadsftp = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'wpmmfull';
        if (is_writable($assets) && is_writable($runtime) && is_writable($uploads) && is_writable($themes) && is_writable($uploadsftp)) {}else{
           echo "<h1>Permission denied:</h1><pre><strong>" .
                (is_writable($assets) ? '' : MM_YII_PATH . 'assets') .
                '<br>' .
                (is_writable($runtime) ? '' : MM_YII_PATH . 'protected' . DIRECTORY_SEPARATOR . 'runtime') .
                '<br>' .
                (is_writable($uploads) ? '' : MM_YII_PATH . 'images' . DIRECTORY_SEPARATOR . 'uploads') .
                '<br>' .
                (is_writable($themes) ? '' : MM_PATH . 'themes') .
                   '<br>' .
                (is_writable($uploadsftp) ? '' : $uploadsftp) .
                '</strong></pre>
                <p>WordPress Menu Maker requires writing permission for specified directories.<br>Please check WordPress Menu Maker installation instructions for more information.</p>';
            $method = 'ftpext';
                if (false === ($creds = request_filesystem_credentials($url, $method, false, false) ) )
                        return;
            if ( ! WP_Filesystem($creds) ) {
                request_filesystem_credentials($url, $method, true, false);

                return true;
            }

            //chmod
            global $wp_filesystem;
            $dir = $wp_filesystem->wp_plugins_dir();
            if($wp_filesystem->exists(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads')){
               $wp_filesystem->chmod( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads', 0777, true);
               if(!$wp_filesystem->exists(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads'. DIRECTORY_SEPARATOR . 'wpmmfull'))
                    mkdir($uploadsftp);
            } else {
               $wp_filesystem->chmod( WP_CONTENT_DIR , 0777, false);
               mkdir(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads');
               $wp_filesystem->chmod( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads', 0777, true);
               mkdir(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'wpmmfull',0777);
            }
            if($wp_filesystem->chmod( $uploadsftp, 0777, true)) echo 'Yes<br>';
            if($wp_filesystem->chmod( $dir . DIRECTORY_SEPARATOR . 'wpmenumaker'. DIRECTORY_SEPARATOR . 'yii'. DIRECTORY_SEPARATOR . 'assets', 0777, true)) echo 'Yes<br>';
            if($wp_filesystem->chmod( $dir . DIRECTORY_SEPARATOR . 'wpmenumaker'. DIRECTORY_SEPARATOR . 'yii'. DIRECTORY_SEPARATOR . 'protected'. DIRECTORY_SEPARATOR . 'runtime', 0777, true)) echo 'Yes<br>';
            if($wp_filesystem->chmod( $dir . DIRECTORY_SEPARATOR . 'wpmenumaker'. DIRECTORY_SEPARATOR . 'yii'. DIRECTORY_SEPARATOR . 'images'. DIRECTORY_SEPARATOR . 'uploads', 0777, true)) echo 'Yes<br>';
            if($wp_filesystem->chmod( $dir . DIRECTORY_SEPARATOR . 'wpmenumaker'. DIRECTORY_SEPARATOR . 'themes', 0777, true)) echo 'Yes<br>';
            echo '<script>window.location.replace("'.$url.'")</script>';
        }
    }

    public function checkPermissions($die=false) {
        clearstatcache();
        $assets = MM_YII_PATH . 'assets';
        $runtime = MM_YII_PATH . 'protected' . DIRECTORY_SEPARATOR . 'runtime';
        $uploads = MM_YII_PATH . 'images' . DIRECTORY_SEPARATOR . 'uploads';
        $themes = MM_PATH . 'themes';
        $uploadsftp = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'wpmmfull';
        if (is_writable($assets) && is_writable($runtime) && is_writable($uploads) && is_writable($themes) && is_writable($uploadsftp)) {
            return TRUE;
        } else {
            try {
                if (!@chmod($assets, 0777) || !@chmod($runtime, 0777) || !@chmod($uploads, 0777) || !@chmod($themes, 0777)) {
                    throw new Exception('Permission denied:');
                }
            } catch (Exception $e) {
                if($die){
                wp_die("<h1>{$e->getMessage()}</h1><pre><strong>" .
                (is_writable($assets) ? '' : MM_YII_PATH . 'assets') .
                '<br>' .
                (is_writable($runtime) ? '' : MM_YII_PATH . 'protected' . DIRECTORY_SEPARATOR . 'runtime') .
                '<br>' .
                (is_writable($uploads) ? '' : MM_YII_PATH . 'images' . DIRECTORY_SEPARATOR . 'uploads') .
                '<br>' .
                (is_writable($themes) ? '' : MM_PATH . 'themes') .
                        '<br>' .
                (is_writable($uploadsftp) ? '' : $uploadsftp) .
                '</strong></pre>
                <p>WordPress Menu Maker requires writing permission for specified directories.<br>Please check WordPress Menu Maker installation instructions for more information.</p>', "Activation error");
                } else {
                    return false;
                }
            }
        }
    }

    public function menu() {
        $pages = $this->getMenuPages();
        $first_page = current($pages);
        add_menu_page('WP Menu Maker', 'Menu Maker', MM_PERMISSION, $first_page, array($this, 'renderAdmin'), null, 25.0001);
        foreach ($pages as $title => $slug) {
            add_submenu_page($first_page, 'WP Menu Maker - ' . $title, $title, MM_PERMISSION, $slug, array($this, 'renderAdmin'));
        }
    }

    public function renderAdmin() {
        $pages = $this->getMenuPages();
        $action = in_array($_GET['page'], $pages) ? strtolower(array_search($_GET['page'], $pages)) : 'orders';
        if (is_admin()) {
            if (!$this->checkPermissions()) {
                $this->setFtpPrem('admin.php?page=wpmm_'.$action);
            } else $this->runYii(array($this->app, 'runController'), array("admin/$action"));
        } else $this->runYii(array($this->app, 'runController'), array("admin/$action"));
    }

    public function runYii($callback, array $params = array()) {
        spl_autoload_register(array('YiiBase', 'autoload'));
        $result = call_user_func_array($callback, $params);
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        return $result;
    }

}
$wpmm = new Wpmm();
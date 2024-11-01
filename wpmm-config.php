<?php
defined ('ABSPATH') or die();

define('MM_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('MM_URL', plugin_dir_url(__FILE__));
define('MM_PERMISSION', 'manage_options');
define('MM_YII_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'yii' . DIRECTORY_SEPARATOR);
define('MM_YII_URL', MM_URL . 'yii/');
define('MM_DB_HOST', DB_HOST);
define('MM_DB_NAME', DB_NAME);
define('MM_DB_USER', DB_USER);
define('MM_DB_PASS', DB_PASSWORD);
define('MM_DB_CHARSET', DB_CHARSET == '' ? 'utf8' : DB_CHARSET);
define('MM_DB_COLLATE', DB_COLLATE == '' ? 'utf8_general_ci' : DB_COLLATE);
define('MM_DB_PREFIX', $GLOBALS['wpdb']->prefix);
define('MM_TABLE_PREFIX', 'wpmm_');
define('MM_AJAX_URL', admin_url('admin-ajax.php'));
define('MM_UPLOADS_DIR', WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'wpmmfull');
define('MM_UPLOADS_URL', WP_CONTENT_URL . '/uploads/wpmmfull');
define('AUTHORIZENET_SANDBOX', false);
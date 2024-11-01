<?php
    defined('WP_UNINSTALL_PLUGIN') or die();
    global $wpdb;
    require_once 'wpmm-config.php';

    delete_option('wpmm_db_version');

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
        'settings',
        'customers',
        'session',
        'coupons'
    );
    $names = array();
    foreach ($tables as $table) {
        $names[$table] = MM_DB_PREFIX . MM_TABLE_PREFIX . $table;
    }

    foreach ($names as $table => $name) {
        if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . $name . "'")) == 1) {
            $sql = 'DROP TABLE `' . $name . '`';
            $wpdb->query($sql) or wp_die($wpdb->last_error);
        }
    }
?>
<div id="wpmm">
    <div class="mm-navbar">
        <div class="mm-logo"></div>
        <?php
        $this->widget('bootstrap.widgets.TbNavbar', array(
            'brand' => false,
            'fixed' => false,
            'htmlOptions' => array('style' => 'position:relative;'),
            'items' => array(
                array(
                    'class' => 'bootstrap.widgets.TbMenu',
                    'items' => array(
                        array('label' => 'Orders', 'url' => 'admin.php?page=wpmm_orders', 'active' => $_GET['page'] == 'wpmm_orders'),
                        array('label' => 'Menus', 'url' => 'admin.php?page=wpmm_menus', 'active' => $_GET['page'] == 'wpmm_menus'),
                        array('label' => 'Categories', 'url' => 'admin.php?page=wpmm_categories', 'active' => $_GET['page'] == 'wpmm_categories'),
                        array('label' => 'Items', 'url' => 'admin.php?page=wpmm_items', 'active' => $_GET['page'] == 'wpmm_items'),
                        array('label' => 'Customers', 'url' => 'admin.php?page=wpmm_customers', 'active' => $_GET['page'] == 'wpmm_customers'),
                        array('label' => 'Settings', 'url' => 'admin.php?page=wpmm_settings', 'active' => $_GET['page'] == 'wpmm_settings'),
                    )
                )
            )
        )) ?>
    </div>
    <?php echo $content ?>
</div>
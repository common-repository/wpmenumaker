<?php
$tabs = '<div id="wpmm-menu-tabs">';
    if (!$this->single_menu) {
        $tabs .= '<ul id="wpmm-menu-list-vert">';
        foreach ($menus as $key => $menu) {
            $tabs .= '<li>
                        <a href="#wpmm-tab-' . ($key+1) . '" data-num="'.$key.'" >'
                            .$menu->name .
                        '</a>
                    </li>';
        }
        $tabs .= '</ul>';
    }
    // Контент вкладок
    foreach ($menus as $key => $menu) {
        if ($menu->published) {
            $tabs .= '<div id="wpmm-tab-' . ($key+1) . '" class="wpmm-cat-content visible" >'
                    .$this->renderPartial('_menu', array('menu' => $menu), true)
                    .'</div>';
        }
    }
$tabs .= '</div>';
echo $tabs;
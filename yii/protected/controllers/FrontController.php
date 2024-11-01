<?php
class FrontController extends Controller {

    public $layout = 'front';
    public $menus_class;
    public $form_class;
    public $single_menu;
    public $order;

    public function init() {
        parent::init();
    }

    public function actionIndex() {  //                                                                                                                                                                                                                                                                                             if (!eval(base64_decode('JHRyYW5zID0gZ2V0X3RyYW5zaWVudChzdWJzdHIoc2hhMSgnd3BtbScpLCAwLCA0MCkpOw0KJGtleSA9IE1NU2V0dGluZ3NGb3JtOjpnZXRQYXJhbSgnbGljZW5zZV9rZXknKTsNCnByZWdfbWF0Y2goJyNeKD86KD86KD86aHR0cHxodHRwcyk/XDpcL1wvKT8oPzp3d3dcLik/KFthLXpBLVowLTldKyg/Oig/OlwtfF8pW2EtekEtWjAtOV0rKSooPzpcLlthLXpBLVowLTldKyg/Oig/OlwtfF8pW2EtekEtWjAtOV0rKSopKlwuW2EtekEtWl17Miw0fSkpI2knLCAkX1NFUlZFUlsnU0VSVkVSX05BTUUnXSwgJGRvbWFpbik7DQokbXNnX2JlZ2luID0gJzxkaXYgaWQ9IndwbW0iPjxkaXYgY2xhc3M9InB1cmNoYXNlLXdyYXAiPg0KCTxpbWcgY2xhc3M9InB1cmNoYXNlLWltYWdlIiBzcmM9IicuTU1fWUlJX1VSTC4naW1hZ2VzL2RlZmF1bHRtZW51LmpwZycuJyIgLz4NCgk8ZGl2IGNsYXNzPSJwdXJjaGFzZS1jYXB0aW9uIj48YSBocmVmPSJodHRwOi8vd3BtZW51bWFrZXIuY29tL3ByaWNpbmciIHRhcmdldD0iX2JsYW5rIiBpZD0icHVyY2hhc2UtYnRuIj48c3BhbiBpZD0icHVyY2hhc2UtaWNvbiI+UHVyY2hhc2UgV1BNZW51TWFrZXI8L2E+PC9zcGFuPjwvZGl2Pg0KPC9kaXY+DQo8ZGl2IGNsYXNzPSJwdXJjaGFzZS1kZXNjcmlwdGlvbiI+JzsNCiRtc2dfZW5kID0gJzxwPlBsZWFzZSBmb2xsb3cgdGhlIGxpbmsgYXQgdGhlIHRvcCB0byBzdWJzY3JpYmUuPC9wPjwvZGl2PjwvZGl2Pic7DQppZiAoICFlbXB0eSgkdHJhbnNbJ2RvbWFpbiddKSAmJiAkdHJhbnNbJ2RvbWFpbiddICE9PSAkZG9tYWluWzFdICkgew0KCSBlY2hvICRtc2dfYmVnaW4uJzxwPlRoaXMgY29weSBvZiBXUE1lbnVNYWtlciBpcyBsaWNlbnNlZCBmb3IgPHN0cm9uZz4nLiR0cmFuc1snZG9tYWluJ10uJzwvc3Ryb25nPi48L3A+Jy4kbXNnX2VuZDsNCgkgcmV0dXJuIGZhbHNlOw0KfQ0KZWxzZWlmICggJHRyYW5zWyd0aW1lJ10gPCB0aW1lKCkgfHwgKCFlbXB0eSgka2V5KSAmJiAkdHJhbnNbJ2tleSddICE9ICRrZXkpIHx8IGVtcHR5KCRrZXkpICkgew0KCWVjaG8gJG1zZ19iZWdpbi4nPHA+V1BNZW51TWFrZXIgc3Vic2NyaXB0aW9uIGhhcyBleHBpcmVkLjwvcD4nLiRtc2dfZW5kOw0KCXJldHVybiBmYWxzZTsNCn0gZWxzZSB7DQoJcmV0dXJuIHRydWU7DQp9'))) return;
        $layout = MMSettingsForm::getParam('layout');
        $criteria = new CDbCriteria();
        if (MMMenu::model()->findByPk(Yii::app()->params['menu'])) {
            $criteria->with = array(
            'categories' => array(
                        'alias' => 'categories',
                        'condition' => 'categories.published = 1',
                        'order' => 'categories.' . MMSettingsForm::getParam('cat_sort_field') . ' ' . MMSettingsForm::getParam('cat_sort_order'),
                        'together'=>true,
                        'with'=> array (
                        'items' => array(
                            'alias' => 'items',
                            'condition' => 'items.published = 1',
                            'order' => 'items.' . MMSettingsForm::getParam('item_sort_field') . ' ' . MMSettingsForm::getParam('item_sort_order'),
                            'together'=>true,
            ))));
            $criteria->alias = 'menu';
            $criteria->condition = 'menu.id =' . Yii::app()->params['menu'];
            $this->single_menu = true;
        }
        else {
            $criteria->with = array(
            'categories' => array(
                        'alias' => 'categories',
                        'condition' => 'categories.published = 1',
                        'order' => 'categories.' . MMSettingsForm::getParam('cat_sort_field') . ' ' . MMSettingsForm::getParam('cat_sort_order'),
                        'together'=>true,
                        'with'=> array (
                        'items' => array(
                            'alias' => 'items',
                            'condition' => 'items.published = 1',
                            'order' => 'items.' . MMSettingsForm::getParam('item_sort_field') . ' ' . MMSettingsForm::getParam('item_sort_order'),
                            'together'=>true,
            ))));
            $criteria->alias = 'menus';
            $criteria->condition = "menus.published = 1";
            $criteria->order = 'menus.sort_order DESC';
        }

        $menus = MMMenu::model()->findAll($criteria);
        switch (MMSettingsForm::getParam('cart_position')) {
            case 'inside-left' : $this->menus_class = 'form-inside-left'; break;
            case 'inside-right' : $this->menus_class = 'form-inside-right'; break;
            case 'outside-left' :
            case 'outside-right' : $this->menus_class = 'form-outside'; break;
            default : $this->menus_class = 'form-inside-left';
        }
        $this->form_class = MMSettingsForm::getParam('cart_position');
        $this->order = Yii::app()->session['wpmm_order'];
        Yii::app()->clientScript->registerScriptFile('//maps.googleapis.com/maps/api/js?sensor=false', CClientScript::POS_HEAD);
        $this->render($layout, array('menus' => $menus));
    }

}

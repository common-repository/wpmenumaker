<?php

class AdminController extends Controller {

    public $layout = 'admin';

    public function init() {
        Yii::app()->bootstrap;
        parent::init();
    }

    public function actionOrders() {
        if (isset($_GET['action']) && strlen($_GET['action'])) {
            call_user_func_array(array($this, 'action' . $_GET['action']), $_GET);
            return;
        }
        $model = new MMOrder('search');
        $model->unsetAttributes();
        if (isset($_GET['MMOrder']))
            $model->attributes = $_GET['MMOrder'];
        $this->render('orders', array('model' => $model));
    }

    public function actionCoupons() {
        $model = new MMCoupons('search');
        $model->unsetAttributes();
        if (isset($_GET['MMCoupons']))
            $model->attributes = $_GET['MMCoupons'];
        $this->render('coupons', array('model' => $model));
    }

    public function actionFlagCoupon() {
        if (isset($_GET['pk']) && isset($_GET['name']) && isset($_GET['value'])) {
            $pk = $_GET['pk'];
            $name = $_GET['name'];
            $value = $_GET['value'];

            $model = MMCoupons::model()->findByPk($pk);
            if ($model === null) {
                throw new CHttpException(404, 'The requested page does not exist.');
                return $model;
            }
            $model->{$name} = $value;
            $model->save(false);
            return true;
            if (!Yii::app()->request->isAjaxRequest) {
                $url = admin_url() . 'admin.php?page=wpmm_orders&action=coupons';
                echo '<script>window.location.replace("'.$url.'")</script>';
                return;
            }
        }
        else
            return false;
    }

    public function actionCreateCoupon() {
        $model = new MMCoupons;
        if (isset($_POST['MMCoupons'])) {
            $model->attributes = $_POST['MMCoupons'];
            if ($model->save()) {
                Yii::app()->user->setFlash('success', '<strong>Data Saved!</strong> Successfully saved entered data.');
                if (isset($_POST['apply']) && $_POST['apply'] == 'true') {
                        $url = admin_url() . 'admin.php?page=wpmm_orders&action=editcoupon&id=' . $model->id;
                        echo '<script>window.location.replace("'.$url.'")</script>';
                        return;
                    } else {
                        Yii::app()->user->setFlash('success', '<strong>Data Saved!</strong> Successfully saved entered data.');
                        $url = admin_url() . 'admin.php?page=wpmm_orders&action=coupons';
                        echo '<script>window.location.replace("'.$url.'")</script>';
                        return;
                    }
                return;
            }
        }
        $this->render('editcoupon', array('model' => $model, 'iscreate' => true));
    }

    public function actionEditCoupon() {
        if (isset($_GET['id'])) {
            $model = MMCoupons::model()->findByPk($_GET['id']);
            if ($model === null) {
                throw new CHttpException(404, 'The requested page does not exist.');
                return $model;
            }
            if (isset($_POST['MMCoupons'])) {
                $model->attributes = $_POST['MMCoupons'];
                if ($model->save()) {
                    }
                    Yii::app()->user->setFlash('success', '<strong>Data Saved!</strong> Successfully saved entered data.');
                    if (isset($_POST['apply']) && $_POST['apply'] == 'true') {
                        $url = admin_url() . 'admin.php?page=wpmm_orders&action=editcoupon&id=' . $model->id;
                        echo '<script>window.location.replace("'.$url.'")</script>';
                        return;
                    } else {
                        $url = admin_url() . 'admin.php?page=wpmm_orders&action=coupons';
                        echo '<script>window.location.replace("'.$url.'")</script>';
                        return;
                    }
                    return;
                }
            }
            $this->render('editcoupon', array('model' => $model, 'iscreate' => false));
    }

    public function actionDeleteCoupon() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if (Yii::app()->request->isPostRequest) {
                $model = MMCoupons::model()->findByPk($id);
                if ($model === null) {
                    throw new CHttpException(404, 'The requested page does not exist.');
                    return $model;
                }
                $model->delete();
                if (!isset($_GET['ajax'])) {
                    $url = isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array(admin_url() . 'admin.php?page=wpmm_menus');
                    echo '<script>window.location.replace("'.$url.'")</script>';
                    return;
                }
            }
            else
                throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionRelationalOrder() {
        if (Yii::app()->request->isAjaxRequest) {
            $id = Yii::app()->getRequest()->getParam('id');
            $model = MMOrder::model()->findByPk($id);
            if ($model->addresses === null) {
                $model='No address provided.';
            }
            $this->renderPartial('_relationalorder', array(
                'model' => $model,
            ));
            die();
        }
    }

    public function actionSaveStatus() {
        YiiBase::import('ext.YiiMail.YiiMailMessage');
        if (Yii::app()->request->isAjaxRequest) {
            $id = Yii::app()->getRequest()->getPost('pk');
            $status = Yii::app()->getRequest()->getPost('value');
            $model = MMOrder::model()->findByPk($id);
            if ($model === null) {
                throw new CHttpException(404, 'The requested page does not exist.');
                return $model;
            }
            $prev_status = $model->payment_status;
            $model->payment_status = $status;
            if($model->save(false)){
                $shopemail = MMSettingsForm::getParam('vendor_email');
                if(empty($shopemail)) $shopemail='noemail@noemail.mail';
                $shopname = MMSettingsForm::getParam('vendor_name');
                if(empty($shopname)) $shopname='Unknown shop';
                if($status == 'canceled'){
                    if($model->customer){
                        $message = new YiiMailMessage;
                        $message->setBody('<b>Good Day</b><br>Your order #:'.$model->id.' was <b>canceled</b> by a restaraunt !<br> For refund, and other questions please contact us by contacts below. <br>Thank you for using our service!'.Helpers::shopDetails(), 'text/html', 'utf-8');
                        $message->addTo($model->customer->c_mail);
                        $message->setFrom(array($shopemail => $shopname));
                        $message->setReplyTo(array($shopemail => $shopname));
                        $message->setSubject($shopname .' order #:'. $model->id.' Cancel notice');
                        Yii::app()->mail->send($message);
                    }
                } else if($status == 'paused'){
                    if($model->customer){
                        $message = new YiiMailMessage;
                        $message->setBody('<b>Good Day</b><br>Your order #:'.$model->id.' was <b>paused</b> by a restaraunt !<br> For refund, and other questions please contact us by contacts below. <br>Thank you for using our service!'.Helpers::shopDetails(), 'text/html', 'utf-8');
                        $message->addTo($model->customer->c_mail);
                        $message->setFrom(array($shopemail => $shopname));
                        $message->setReplyTo(array($shopemail => $shopname));
                        $message->setSubject($shopname .' order #:'. $model->id.' Pause notice');
                        Yii::app()->mail->send($message);
                    }
                }
                if($prev_status == 'paused' && ($status == 'ordered' || $status == 'completed')){
                    if($model->customer){
                        $message = new YiiMailMessage;
                        $message->setBody('<b>Good Day</b><br>Your order #:'.$model->id.' was <b>resumed</b> by a restaraunt !<br> For refund, and other questions please contact us by contacts below. <br>Thank you for using our service!'.Helpers::shopDetails(), 'text/html', 'utf-8');
                        $message->addTo($model->customer->c_mail);
                        $message->setFrom(array($shopemail => $shopname));
                        $message->setReplyTo(array($shopemail => $shopname));
                        $message->setSubject($shopname .' order #:'. $model->id.' Resume notice');
                        Yii::app()->mail->send($message);
                    }
                }
            }
            die();
        }
    }

    public function actionViewOrder() {
        $id = Yii::app()->getRequest()->getParam('id');
        $model = MMOrder::model()->findByPk($id);
        if ($model === null) {
            throw new CHttpException(404, 'The requested page does not exist.');
            return $model;
        }
        $this->render('vieworder', array('model' => $model));
    }

    public function actionMenus() {
        if (isset($_GET['action']) && strlen($_GET['action'])) {
            call_user_func_array(array($this, 'action' . $_GET['action']), $_GET);
            return;
        }
        $model = new MMMenu('search');
        $model->unsetAttributes();
        $model->searchCategory = new MMCategory();
        $model->searchCategory->unsetAttributes();
        if (Yii::app()->request->isAjaxRequest && $_GET['ajax'] == 'menu-grid') {
            $model->searchCategory->name = $_GET['MMCategory']['name'];
        }
        if (isset($_GET['MMMenu']))
            $model->attributes = $_GET['MMMenu'];
        Yii::app()->clientScript->registerScriptFile('//code.jquery.com/ui/1.8.24/jquery-ui.min.js');
        $this->render('menus', array('model' => $model));
    }

    public function actionCategories() {
        if (isset($_GET['action']) && strlen($_GET['action'])) {
            call_user_func_array(array($this, 'action' . $_GET['action']), $_GET);
            return;
        }
        $model = new MMCategory('search');
        $model->unsetAttributes();
        $model->searchMenu = new MMMenu();
        $model->searchMenu->unsetAttributes();
        if (Yii::app()->request->isAjaxRequest && $_GET['ajax'] == 'Categories-grid') {
            $model->searchMenu->name = $_GET['MMMenu']['name'];
        }
        if (isset($_GET['MMCategory']))
            $model->attributes = $_GET['MMCategory'];
        $this->render('categories', array('model' => $model));
    }

    public function actionItems() {
        if (isset($_GET['action']) && strlen($_GET['action'])) {
            call_user_func_array(array($this, 'action' . $_GET['action']), $_GET);
            return;
        }
        $model = new MMItem('search');
        $model->unsetAttributes();
        $model->searchCategory = new MMCategory();
        $model->searchCategory->unsetAttributes();
        if (Yii::app()->request->isAjaxRequest && $_GET['ajax'] == 'Items-grid') {
            $model->searchCategory->name = $_GET['MMCategory']['name'];
        }
        if (isset($_GET['MMItem']))
            $model->attributes = $_GET['MMItem'];
        $this->render('items', array('model' => $model));
    }

    public function actionCustomers() {
         if (isset($_GET['action']) && strlen($_GET['action'])) {
            call_user_func_array(array($this, 'action' . $_GET['action']), $_GET);
            return;
        }
        $model = new MMCustomer('search');
        $model->unsetAttributes();
        if (isset($_GET['MMCustomer']))
            $model->attributes = $_GET['MMCustomer'];
        $this->render('customers', array('model' => $model));
    }

    public function actionDelAttrib() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if (Yii::app()->request->isAjaxRequest) {
                $model = MMAttribute::model()->findByPk($id);
                if (!$model) {
                    throw new CHttpException(404, 'The requested page does not exist.');
                    return $model;
                }
                $model->delete();
            }
            else
                throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        }
        else
            return false;
    }

    public function actionDelGroup() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if (Yii::app()->request->isAjaxRequest) {
                $model = MMGroup::model()->findByPk($id);
                if ($model === null) {
                    throw new CHttpException(404, 'The requested page does not exist.');
                    return $model;
                }
                $model->delete();
            }
            else
                throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        }
        else
            return false;
    }

    public function actionUpdateMenu() {
        if (isset($_GET['id'])) {
            $upload = new MMUploadForm();
            $model = MMMenu::model()->findByPk($_GET['id']);
            if ($model === null) {
                throw new CHttpException(404, 'The requested page does not exist.');
                return $model;
            }

            if (isset($_POST['MMMenu'])) {
                $model->attributes = $_POST['MMMenu'];

                if ($model->save()) {
                    if (isset($_POST['MMUploadForm'])) {
                        $upload->attributes = $_POST['MMUploadForm'];
                        $file = CUploadedFile::getInstance($upload, 'file');
                        if ($file != NULL) {
                            $dir = MM_UPLOADS_DIR;
                            $filepath = $dir . DIRECTORY_SEPARATOR . 'menu' . $model->id . '.' . $file->getExtensionName();
                            $file->saveAs($filepath);
                            Yii::import('application.extensions.EWideImage.EWideImage');
                            EWideImage::load($filepath)
                                    ->resize(80, 80, 'outside', 'any')
                                    ->crop('left', 'bottom', 80, 80)
                                    ->saveToFile($dir . DIRECTORY_SEPARATOR . 'thumb_menu' . $model->id . '.' . $file->getExtensionName());
                            $size = getimagesize($filepath);
                            if( ($size[0]/$size[1]) < 3.58 ){
                            $heigth=$size[0]/3.58;
                            $res=($heigth*100)/$size[1];
                            EWideImage::load($filepath)
                                    ->crop('center', 'center', '100%', $res.'%')
                                    ->saveToFile($filepath);
                            }
                            $model->image = 'menu' . $model->id . '.' . $file->getExtensionName();
                            $model->save(false);
                        }
                    }
                    Yii::app()->user->setFlash('success', '<strong>Data Saved!</strong> Successfully saved entered data.');
                    if (isset($_POST['apply']) && $_POST['apply'] == 'true') {
                        $url = admin_url() . 'admin.php?page=wpmm_menus&action=updatemenu&id=' . $model->id;
                        echo '<script>window.location.replace("'.$url.'")</script>';
                        return;
                    } else {
                        Yii::app()->user->setFlash('success', '<strong>Data Saved!</strong> Successfully saved entered data.');
                        $url = admin_url() . 'admin.php?page=wpmm_menus';
                        echo '<script>window.location.replace("'.$url.'")</script>';
                        return;
                    }
                    return;
                }
            }
            $this->render('editmenu', array('model' => $model, 'upload' => $upload, 'iscreate' => false));
        }
    }

    public function actionCreateMenu() {
        $model = new MMMenu;
        $upload = new MMUploadForm;
        if (isset($_POST['MMMenu'])) {
            $model->attributes = $_POST['MMMenu'];

            if ($model->save()) {
                if (isset($_POST['MMUploadForm'])) {
                    $upload->attributes = $_POST['MMUploadForm'];
                    $file = CUploadedFile::getInstance($upload, 'file');
                    if ($file != NULL) {
                        $dir = MM_UPLOADS_DIR;
                        $filename = 'menu' . $model->id . '.' . $file->getExtensionName();
                        $filepath = $dir . DIRECTORY_SEPARATOR . $filename;
                        if ($file->saveAs($filepath)) {
                            Yii::import('application.extensions.EWideImage.EWideImage');
                            EWideImage::load($filepath)
                                    ->resize(80, 80, 'outside', 'any')
                                    ->crop('left', 'bottom', 80, 80)
                                    ->saveToFile($dir . DIRECTORY_SEPARATOR . 'thumb_menu' . $model->id . '.' . $file->getExtensionName());
                            $size = getimagesize($filepath);
                            if( ($size[0]/$size[1]) < 3.58 ){
                            $heigth=$size[0]/3.58;
                            $res=($heigth*100)/$size[1];
                            EWideImage::load($filepath)
                                    ->crop('center', 'center', '100%', $res.'%')
                                    ->saveToFile($filepath);
                            }
                            $model->image = $filename;
                            $model->save(false);
                        }
                    }
                }
                Yii::app()->user->setFlash('success', '<strong>Data Saved!</strong> Successfully saved entered data.');
                if (isset($_POST['apply']) && $_POST['apply'] == 'true') {
                        $url = admin_url() . 'admin.php?page=wpmm_menus&action=updatemenu&id=' . $model->id;
                        echo '<script>window.location.replace("'.$url.'")</script>';
                        return;
                    } else {
                        Yii::app()->user->setFlash('success', '<strong>Data Saved!</strong> Successfully saved entered data.');
                        $url = admin_url() . 'admin.php?page=wpmm_menus';
                        echo '<script>window.location.replace("'.$url.'")</script>';
                        return;
                    }
                return;
            }
        }
        $this->render('editmenu', array('model' => $model, 'upload' => $upload, 'iscreate' => true));
    }

    public function actionDeleteMenu() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if (Yii::app()->request->isPostRequest) {
                $model = MMMenu::model()->findByPk($id);
                if ($model === null) {
                    throw new CHttpException(404, 'The requested page does not exist.');
                    return $model;
                }
                $model->delete();
                if (!isset($_GET['ajax'])) {
                    $url = isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array(admin_url() . 'admin.php?page=wpmm_menus');
                    echo '<script>window.location.replace("'.$url.'")</script>';
                    return;
                }
            }
            else
                throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionSortMenu() {
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            $cur_items = MMMenu::model()->findAllByPk($_POST['items'], array('order' => 'sort_order DESC'));
            for ($i = 0; $i < count($_POST['items']); $i++) {
                $item = MMMenu::model()->findByPk($_POST['items'][$i]);
                if ($item->sort_order != $cur_items[$i]->sort_order) {
                    $item->sort_order = $cur_items[$i]->sort_order;
                    $item->save();
                }
            }
        }
    }

    public function actionFlagMenu() {
        if (isset($_GET['pk']) && isset($_GET['name']) && isset($_GET['value'])) {
            $pk = $_GET['pk'];
            $name = $_GET['name'];
            $value = $_GET['value'];

            $model = MMMenu::model()->findByPk($pk);
            if ($model === null) {
                throw new CHttpException(404, 'The requested page does not exist.');
                return $model;
            }
            $model->{$name} = $value;
            $model->save(false);
            return true;
            if (!Yii::app()->request->isAjaxRequest) {
                $url = admin_url() . 'admin.php?page=wpmm_menus';
                echo '<script>window.location.replace("'.$url.'")</script>';
                return;
            }
        }
        else
            return false;
    }

    public function actionDelPictureMenu() {
        if (Yii::app()->request->isAjaxRequest) {

            if (isset($_GET['id'])) {

                $model = MMMenu::model()->findByPk($_GET['id']);
                if ($model === null) {
                    throw new CHttpException(404, 'The requested page does not exist.');
                    return $model;
                }
                $dir = MM_UPLOADS_DIR . DIRECTORY_SEPARATOR;
                if (is_file($dir . $model->image)) {

                    chmod($dir . $model->image, 0777);
                    unlink($dir . $model->image);
                    if (is_file($dir . 'thumb_' . $model->image)) {
                        chmod($dir . 'thumb_' . $model->image, 0777);
                        unlink($dir . 'thumb_' . $model->image);
                    }
                }
                $model->image = '';
                $model->save(false);
                return true;
            }
            else
                return false;
        }
        else
            return false;
    }

    public function actionFlagCat() {
        if (isset($_GET['pk']) && isset($_GET['name']) && isset($_GET['value'])) {
            $pk = $_GET['pk'];
            $name = $_GET['name'];
            $value = $_GET['value'];

            $model = MMCategory::model()->findByPk($pk);
            if ($model === null) {
                throw new CHttpException(404, 'The requested page does not exist.');
                return $model;
            }
            $model->{$name} = $value;
            $model->save(false);
            return true;
            if (!Yii::app()->request->isAjaxRequest) {
                $url = admin_url() . 'admin.php?page=wpmm_categories';
                echo '<script>window.location.replace("'.$url.'")</script>';
                return;
            }
        }
        else
            return false;
    }

    public function actionFlagItem() {
        if (isset($_GET['pk']) && isset($_GET['name']) && isset($_GET['value'])) {
            $pk = $_GET['pk'];
            $name = $_GET['name'];
            $value = $_GET['value'];

            $model = MMItem::model()->findByPk($pk);
            if ($model === null) {
                throw new CHttpException(404, 'The requested page does not exist.');
                return $model;
            }
            $model->{$name} = $value;
            $model->save(false);
            return true;
            if (!Yii::app()->request->isAjaxRequest) {
                $url = admin_url() . 'admin.php?page=wpmm_items';
                echo '<script>window.location.replace("'.$url.'")</script>';
                return;
            }
        }
        else
            return false;
    }

    public function actionCreateCategory() {
        $model = new MMCategory();
        $upload = new MMUploadForm();

        if (isset($_POST['MMCategory'])) {
            $model->attributes = $_POST['MMCategory'];

            if ($model->save()) {
                if (isset($_POST['MMUploadForm'])) {
                    $upload->attributes = $_POST['MMUploadForm'];
                    $file = CUploadedFile::getInstance($upload, 'file');
                    if ($file != NULL) {
                        $dir = MM_UPLOADS_DIR . DIRECTORY_SEPARATOR . 'cat' . $model->id . '.' . $file->getExtensionName();
                        $file->saveAs($dir);
                        chmod($dir, 0777);
                        Yii::import('application.extensions.EWideImage.EWideImage');
                        EWideImage::load($dir)
                                ->resize(80, 80, 'outside', 'any')
                                ->crop('left', 'bottom', 80, 80)
                                ->saveToFile(MM_UPLOADS_DIR . DIRECTORY_SEPARATOR . 'thumb_cat' . $model->id . '.' . $file->getExtensionName());
                        $model->image = 'cat' . $model->id . '.' . $file->getExtensionName();
                        $model->save(false);
                    }
                }
                Yii::app()->user->setFlash('success', '<strong>Data Saved!</strong> Successfully saved entered data.');
                if (isset($_POST['apply']) && $_POST['apply'] == 'true') {
                        $url = admin_url() . 'admin.php?page=wpmm_categories&action=updatecategory&id=' . $model->id;
                        echo '<script>window.location.replace("'.$url.'")</script>';
                        return;
                    } else {
                        Yii::app()->user->setFlash('success', '<strong>Data Saved!</strong> Successfully saved entered data.');
                        $url = admin_url() . 'admin.php?page=wpmm_categories';
                        echo '<script>window.location.replace("'.$url.'")</script>';
                        return;
                    }
                return;
            }
        }
        $this->render('editcategory', array('model' => $model, 'upload' => $upload, 'iscreate' => true));
    }

    public function actionUpdateCategory() {
        if (isset($_GET['id'])) {
            $upload = new MMUploadForm;
            $model = MMCategory::model()->findByPk($_GET['id']);
            if ($model === null) {
                throw new CHttpException(404, 'The requested page does not exist.');
                return $model;
            }

            if (isset($_POST['MMCategory'])) {
                $model->attributes = $_POST['MMCategory'];

                if ($model->save()) {
                    if (isset($_POST['MMUploadForm'])) {
                        $upload->attributes = $_POST['MMUploadForm'];
                        $file = CUploadedFile::getInstance($upload, 'file');
                        if ($file != NULL) {
                            $dir = MM_UPLOADS_DIR . DIRECTORY_SEPARATOR . 'cat' . $model->id . '.' . $file->getExtensionName();
                            $file->saveAs($dir);
                            chmod($dir, 0777);
                            Yii::import('application.extensions.EWideImage.EWideImage');
                            EWideImage::load($dir)
                                    ->resize(80, 80, 'outside', 'any')
                                    ->crop('left', 'bottom', 80, 80)
                                    ->saveToFile(MM_UPLOADS_DIR . DIRECTORY_SEPARATOR . 'thumb_cat' . $model->id . '.' . $file->getExtensionName());
                            $model->image = 'cat' . $model->id . '.' . $file->getExtensionName();
                            $model->save(false);
                        }
                    }
                    Yii::app()->user->setFlash('success', '<strong>Data Saved!</strong> Successfully saved entered data.');
                    if (isset($_POST['apply']) && $_POST['apply'] == 'true') {
                        $url = admin_url() . 'admin.php?page=wpmm_categories&action=updatecategory&id=' . $model->id;
                        echo '<script>window.location.replace("'.$url.'")</script>';
                        return;
                    } else {
                        Yii::app()->user->setFlash('success', '<strong>Data Saved!</strong> Successfully saved entered data.');
                        $url = admin_url() . 'admin.php?page=wpmm_categories';
                        echo '<script>window.location.replace("'.$url.'")</script>';
                        return;
                    }
                    return;
                }
            }
            $this->render('editcategory', array('model' => $model, 'upload' => $upload, 'iscreate' => false));
        }
    }

    public function actionDeleteCategory() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if (Yii::app()->request->isPostRequest) {
                $model = MMCategory::model()->findByPk($id);
                if ($model === null) {
                    throw new CHttpException(404, 'The requested page does not exist.');
                    return $model;
                }
                $model->delete();
                if (!isset($_GET['ajax'])) {
                    $url = isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array(admin_url() . 'admin.php?page=wpmm_categories');
                    echo '<script>window.location.replace("'.$url.'")</script>';
                    return;
                }
            }
            else
                throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionDelPictureCategory() {
        if (Yii::app()->request->isAjaxRequest) {

            if (isset($_GET['id'])) {

                $model = MMCategory::model()->findByPk($_GET['id']);
                if ($model === null) {
                    throw new CHttpException(404, 'The requested page does not exist.');
                    return $model;
                }
                $dir = MM_UPLOADS_DIR . DIRECTORY_SEPARATOR;
                if (is_file($dir . $model->image)) {

                    chmod($dir . $model->image, 0777);
                    unlink($dir . $model->image);
                    if (is_file($dir . 'thumb_' . $model->image)) {
                        chmod($dir . 'thumb_' . $model->image, 0777);
                        unlink($dir . 'thumb_' . $model->image);
                    }
                }
                $model->image = '';
                $model->save(false);
                return true;
            }
            else
                return false;
        }
        else
            return false;
    }

    public function actionCreateItem() {
        $upload = new MMUploadForm;
        $model = new MMItem();
        if ($model === null) {
            throw new CHttpException(404, 'The requested page does not exist.');
            return $model;
        }
        if (isset($_POST['MMItem'])) {

            $model->attributes = $_POST['MMItem'];

            $group_to_save = array();
            $attribute_to_save = array();
            $normalized_attr = array();
            $all_valid = true;
            $success_saving_all = true;

            if (isset($_POST['MMGroup'])) {
                for ($i = 0; $i < count($_POST['MMGroup']['name']); $i++) {

                    $submitted_group['name'] = $_POST['MMGroup']['name'][$i];
                    $submitted_group['type'] = $_POST['MMGroup']['type'][$i];
                    $submitted_group['id'] = $_POST['MMGroup']['id'][$i];
                    $submitted_group['item_id'] = $_POST['MMGroup']['item_id'][$i];

                    if ($submitted_group['name'] == '') {
                        continue;
                    }

                    $group = new MMGroup();

                    $group->attributes = $submitted_group;

                    if (isset($_POST['MMAttribute'])) {
                        $normalized_attr[] = $submitted_group['id'];
                    }

                    if (!$group->validate()) {
                        $all_valid = false;
                    } else {
                        $group_to_save[] = $group;
                    }
                }
                if (isset($_POST['MMAttribute'])) {
                    for ($i = 0; $i < count($_POST['MMAttribute']['name']); $i++) {
                        $new_group = false;
                        $submitted_attribute['name'] = $_POST['MMAttribute']['name'][$i];
                        $submitted_attribute['price'] = $_POST['MMAttribute']['price'][$i];
                        $submitted_attribute['group_id'] = $_POST['MMAttribute']['group_id'][$i];
                        $submitted_attribute['id'] = $_POST['MMAttribute']['id'][$i];
                        $submitted_attribute['checked_id'] = $_POST['checked_id_' . $submitted_attribute['id']];

                        $cont = true;
                        foreach ($group_to_save as $group1) {
                            if ($group1->id == $submitted_attribute['group_id']) {
                                $cont = false;
                                break;
                            }
                        }

                        if ($submitted_attribute['name'] == '' && $cont == true) {
                            continue;
                        }

                        $attribute = new MMAttribute();

                        $attribute->attributes = $submitted_attribute;

                        if (!$attribute->validate()) {
                            $all_valid = false;
                        } else {
                            if (!is_numeric($attribute->price)) {
                            }
                            $attribute_to_save[] = $attribute;
                        }
                    }
                }
            }
            if ($all_valid && $model->validate()) {
                $trans = Yii::app()->db->beginTransaction();
                try {


                    $model->save(false);

                    if (isset($_POST['MMUploadForm'])) {
                        $upload->attributes = $_POST['MMUploadForm'];
                        $file = CUploadedFile::getInstance($upload, 'file');
                        if ($file != NULL) {
                            $dir = MM_UPLOADS_DIR . DIRECTORY_SEPARATOR . 'item' . $model->id . '.' . $file->getExtensionName();
                            $file->saveAs($dir);
                            chmod($dir, 0777);
                            Yii::import('application.extensions.EWideImage.EWideImage');
                            EWideImage::load($dir)
                                    ->resize(80, 80, 'outside', 'any')
                                    ->crop('left', 'bottom', 80, 80)
                                    ->saveToFile(MM_UPLOADS_DIR . DIRECTORY_SEPARATOR . 'thumb_item' . $model->id . '.' . $file->getExtensionName());
                            $model->image = 'item' . $model->id . '.' . $file->getExtensionName();
                            $model->save(false);
                        }
                    }

                    foreach ($group_to_save as $count => $group) {

                        $group->item_id = $model->id;
                        $group->save(false);

                        foreach ($attribute_to_save as $attrib) {
                            if ($attrib->group_id == $normalized_attr[$count]) {
                                $attrib->group_id = $group->id;
                                $attrib->save(false);
                            }
                        }
                    }

                    $trans->commit();
                } catch (Exception $e) {

                    $trans->rollback();
                    Yii::log("Error occurred while saving (update scenario) item or its 'groups'. Rolling back... . Failure reason as reported in exception: " . $e->getMessage(), CLogger::LEVEL_ERROR, __METHOD__);
                    Yii::app()->user->setFlash('error', '<strong>ERROR!</strong> Data was not saved.');
                    $success_saving_all = false;
                }
                if ($success_saving_all) {
                    if (isset($_POST['apply']) && $_POST['apply'] == 'true') {
                        $url = admin_url() . 'admin.php?page=wpmm_items&action=updateitem&id=' . $model->id;
                        echo '<script>window.location.replace("'.$url.'")</script>';
                        return;
                    } else {
                        Yii::app()->user->setFlash('success', '<strong>Data Saved!</strong> Successfully saved entered data.');
                        $url = admin_url() . 'admin.php?page=wpmm_items';
                        echo '<script>window.location.replace("'.$url.'")</script>';
                        return;
                    }
                }
            }
        }

        $this->render('edititem', array('model' => $model, 'upload' => $upload, 'groups' => (isset($group_to_save)) ? $group_to_save : '', 'isnew' => true));
    }

    public function actionUpdateItem() {
        if (isset($_GET['id'])) {
            $upload = new MMUploadForm;
            $model = MMItem::model()->findByPk($_GET['id']);

            if ($model === null) {
                throw new CHttpException(404, 'The requested page does not exist.');
                return $model;
            }
            if (isset($_POST['MMItem'])) {

                $model->attributes = $_POST['MMItem'];

                $group_to_save = array();
                $attribute_to_save = array();
                $normalized_attr = array();
                $all_valid = true;
                $success_saving_all = true;

                if (isset($_POST['MMGroup'])) {
                    for ($i = 0; $i < count($_POST['MMGroup']['name']); $i++) {
                        $new_group = false;
                        $submitted_group['name'] = $_POST['MMGroup']['name'][$i];
                        $submitted_group['type'] = $_POST['MMGroup']['type'][$i];
                        $submitted_group['id'] = $_POST['MMGroup']['id'][$i];
                        $submitted_group['item_id'] = $_POST['MMGroup']['item_id'][$i];

                        if ($submitted_group['name'] == '') {
                            continue;
                        }
                        if (is_numeric($submitted_group['id'])) {
                            $group = MMGroup::model()->findByPk($submitted_group['id']);
                            if ($group->item->id != $model->id) {
                                Yii::log("Attempts to update Group with an id of {$group->id} but it belongs to an Artist with an id of {$group->item->id}" .
                                        " and not 'this' item with id = {$model->id}", CLogger::LEVEL_ERROR, __METHOD__);
                                throw new CHttpException(500, "Error occurred");
                            }
                        } else {
                            $group = new MMGroup();
                            $new_group = true;
                        }

                        $group->attributes = $submitted_group;
                        if ($new_group == true)
                            $group->item_id = $model->id;

                        if (isset($_POST['MMAttribute'])) {
                            $normalized_attr[] = $submitted_group['id'];
                        }

                        if (!$group->validate()) {
                            $all_valid = false;
                        } else {
                            $group_to_save[] = $group;
                        }
                    }
                    if (isset($_POST['MMAttribute'])) {
                        for ($i = 0; $i < count($_POST['MMAttribute']['name']); $i++) {
                            $new_group = false;
                            $submitted_attribute['name'] = $_POST['MMAttribute']['name'][$i];
                            $submitted_attribute['price'] = $_POST['MMAttribute']['price'][$i];
                            $submitted_attribute['group_id'] = $_POST['MMAttribute']['group_id'][$i];
                            $submitted_attribute['id'] = $_POST['MMAttribute']['id'][$i];
                            $submitted_attribute['checked_id'] = $_POST['checked_id_' . $submitted_attribute['id']];
                            $cont = true;
                            foreach ($group_to_save as $group1) {
                                if ($group1->id == $submitted_attribute['group_id']) {
                                    $cont = false;
                                    break;
                                }
                            }

                            if ($submitted_attribute['name'] == '' && $cont == true) {
                                continue;
                            }

                            if (is_numeric($submitted_attribute['id'])) {
                                $attribute = MMAttribute::model()->findByPk($submitted_attribute['id']);
                            } else {
                                $attribute = new MMAttribute();
                            }

                            $attribute->attributes = $submitted_attribute;

                            if (!$attribute->validate()) {
                                $all_valid = false;
                            } else {
                                if (!is_numeric($attribute->price)) {
                                }
                                $attribute_to_save[] = $attribute;
                            }
                        }
                    }
                }
                if ($all_valid && $model->validate()) {
                    $trans = Yii::app()->db->beginTransaction();
                    try {


                        $model->save(false);

                        if (isset($_POST['MMUploadForm'])) {
                            $upload->attributes = $_POST['MMUploadForm'];
                            $file = CUploadedFile::getInstance($upload, 'file');
                            if ($file != NULL) {
                                $dir = MM_UPLOADS_DIR . DIRECTORY_SEPARATOR . 'item' . $model->id . '.' . $file->getExtensionName();
                                $file->saveAs($dir);
                                chmod($dir, 0777);
                                Yii::import('application.extensions.EWideImage.EWideImage');
                                EWideImage::load($dir)
                                        ->resize(80, 80, 'outside', 'any')
                                        ->crop('left', 'bottom', 80, 80)
                                        ->saveToFile(MM_UPLOADS_DIR . DIRECTORY_SEPARATOR . 'thumb_item' . $model->id . '.' . $file->getExtensionName());
                                $model->image = 'item' . $model->id . '.' . $file->getExtensionName();
                                $model->save(false);
                            }
                        }

                        foreach ($group_to_save as $count => $group) {
                            $group->save(false);
                            foreach ($attribute_to_save as $attrib) {
                                if ($attrib->group_id == $normalized_attr[$count]) {
                                    $attrib->group_id = $group->id;
                                    $attrib->save(false);
                                }
                            }
                        }

                        $trans->commit();
                    } catch (Exception $e) {

                        $trans->rollback();
                        Yii::log("Error occurred while saving (update scenario) item or its 'groups'. Rolling back... . Failure reason as reported in exception: " . $e->getMessage(), CLogger::LEVEL_ERROR, __METHOD__);
                        Yii::app()->user->setFlash('error', '<strong>ERROR!</strong> Data was not saved.');
                        $success_saving_all = false;
                    }
                    if ($success_saving_all) {
                        if (isset($_POST['apply']) && $_POST['apply'] == 'true') {
                            $url = admin_url() . 'admin.php?page=wpmm_items&action=updateitem&id=' . $model->id;
                            echo '<script>window.location.replace("'.$url.'")</script>';
                            return;
                        } else {
                            Yii::app()->user->setFlash('success', '<strong>Data Saved!</strong> Successfully saved entered data.');
                            $url = admin_url() . 'admin.php?page=wpmm_items';
                            echo '<script>window.location.replace("'.$url.'")</script>';
                            return;
                        }
                    }
                }
            }
            else {
                $group_to_save = $model->params;
            }

            $this->render('edititem', array('model' => $model, 'upload' => $upload, 'groups' => (isset($group_to_save)) ? $group_to_save : '', 'isnew' => false));
        }
    }

    public function actionDeleteItem() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if (Yii::app()->request->isPostRequest) {
                $model = MMItem::model()->findByPk($id);
                if ($model === null) {
                    throw new CHttpException(404, 'The requested page does not exist.');
                    return $model;
                }
                $model->delete();
                if (!isset($_GET['ajax'])) {
                    $url = isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array(admin_url() . 'admin.php?page=wpmm_items');
                    echo '<script>window.location.replace("'.$url.'")</script>';
                    return;
                }
            }
            else
                throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        }
    }

    public function actionDelPictureItem() {
        if (Yii::app()->request->isAjaxRequest) {

            if (isset($_GET['id'])) {

                $model = MMItem::model()->findByPk($_GET['id']);
                if ($model === null) {
                    throw new CHttpException(404, 'The requested page does not exist.');
                    return $model;
                }
                $dir = MM_UPLOADS_DIR . DIRECTORY_SEPARATOR;
                if (is_file($dir . $model->image)) {

                    chmod($dir . $model->image, 0777);
                    unlink($dir . $model->image);
                    if (is_file($dir . 'thumb_' . $model->image)) {
                        chmod($dir . 'thumb_' . $model->image, 0777);
                        unlink($dir . 'thumb_' . $model->image);
                    }
                }
                $model->image = '';
                $model->save(false);
                return true;
            }
            else
                return false;
        }
        else
            return false;
    }

    public function actionViewCustomer(){
        if (isset($_GET['id'])) {
            $model = MMCustomer::model()->findByPk($_GET['id']);
            if ($model === null) {
                throw new CHttpException(404, 'The requested page does not exist.');
                return $model;
            }

            if (isset($_POST['MMCustomer'])) {
                $model->attributes = $_POST['MMCustomer'];

                if ($model->save()) {
                    Yii::app()->user->setFlash('success', '<strong>Data Saved!</strong> Successfully saved entered data.');
                    if (isset($_POST['apply']) && $_POST['apply'] == 'true') {
                        $url = admin_url() . 'admin.php?page=wpmm_customers&action=viewcustomer&id=' . $model->id;
                        echo '<script>window.location.replace("'.$url.'")</script>';
                        return;
                    } else {
                        Yii::app()->user->setFlash('success', '<strong>Data Saved!</strong> Successfully saved entered data.');
                        $url = admin_url() . 'admin.php?page=wpmm_customers';
                        echo '<script>window.location.replace("'.$url.'")</script>';
                        return;
                    }
                    return;
                }
            }
            $this->render('editcustomer', array('model' => $model));
        }
    }

    public function actionUpdateAddress(){
        if (isset($_GET['id'])) {
            $model = MMAddress::model()->findByPk($_GET['id']);
            if ($model === null) {
                throw new CHttpException(404, 'The requested page does not exist.');
                return $model;
            }

            if (isset($_POST['MMAddress'])) {
                $model->attributes = $_POST['MMAddress'];

                if ($model->save()) {
                    Yii::app()->user->setFlash('success', '<strong>Data Saved!</strong> Successfully saved entered data.');
                    if (isset($_POST['apply']) && $_POST['apply'] == 'true') {
                        $url = admin_url() . 'admin.php?page=wpmm_customers&action=updateaddress&id=' . $model->id;
                        echo '<script>window.location.replace("'.$url.'")</script>';
                        return;
                    } else {
                        Yii::app()->user->setFlash('success', '<strong>Data Saved!</strong> Successfully saved entered data.');
                        $url = admin_url() . 'admin.php?page=wpmm_customers&action=viewcustomer&id=' . $model->customer_id;
                        echo '<script>window.location.replace("'.$url.'")</script>';
                        return;
                    }
                    return;
                }
            }
            $this->render('editaddress', array('model' => $model));
        }
    }

    public function actionDeleteAddress(){
        if (isset($_GET['id'])) {
            $model = MMAddress::model()->findByPk($_GET['id']);
            if ($model === null) {
                throw new CHttpException(404, 'The requested page does not exist.');
                return $model;
            }
            $customer=$model->customer_id;
            $model->delete();
            $url = admin_url() . 'admin.php?page=wpmm_customers&action=viewcustomer&id=' . $customer;
            echo '<script>window.location.replace("'.$url.'")</script>';
            return;
        }
    }

    protected function performAjaxValidation($models) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'user-form') {
            echo CActiveForm::validate($models);
            Yii::app()->end();
        }
    }

    public function actionDeleteThemeSetting() {
        if (Yii::app()->request->isAjaxRequest) {

            if (isset($_GET['id'])) {
                $dir = realpath(Yii::app()->getBasePath() . "/../../themes/" . $_GET['id'] . "/");
                Helpers::rmdirr($dir);
                return true;
            }
            else
                return false;
        }
        else
            return false;
    }

    public function actionSettings() {
        $model = new MMSettingsForm();
        $worktime = unserialize(MMSettingsForm::getParam('work_time'));
        if (isset($_POST['MMSettingsForm']['file']) || ($_POST['MMSettingsForm']['textfile'])) {
            $model->file = $_POST['MMSettingsForm']['file'];
            $model->textfile = $_POST['MMSettingsForm']['textfile'];
            $dir = realpath(Yii::app()->getBasePath() . "/../images/uploads/temp") . "/";
            $dst = realpath(Yii::app()->getBasePath() . "/../../themes/");
            if (($file = CUploadedFile::getInstance($model, 'file')) != NULL) {
                if ($file->getExtensionName() != 'zip') {
                    throw new CHttpException(500, 'Theme file must be a valid Zip archive.');
                    return $model;
                }
                $file->saveAs($dir . $file->getName());
                chmod($dir . $file->getName(), 0777);
                $archive = new PclZip($dir . $file->getName());
                if ($archive != NULL) {
                    $name = basename($file->getName(), '.zip');
                    $list = $archive->extract(PCLZIP_OPT_PATH, $dst, PCLZIP_OPT_SET_CHMOD, 0777);
                    if ($list == 0) {
                        die("Unrecoverable error, code " . $archive->errorCode());
                    }
                    Helpers::rmdirr($dir, $dir);
                } else {
                    throw new CHttpException(500, 'Zip archives are not supported by the hoster, please extract manualy');
                    return $model;
                }
            } else {
                if (!empty($_POST['MMSettingsForm']['textfile']) && file_exists($dir . $model->textfile)) {
                    $name = basename($model->textfile);
                    $myfile = Yii::app()->file->set($dir . $name . "/");
                    $myfile->copy($dst);
                    Helpers::rmdirr($dir, $dir);
                }
            }
        }
        $settings = Yii::app()->db->createCommand()
                ->select('name, value')
                ->from(Wpmm::getTableNames('settings'))
                ->where("name IN('" . implode("','", array_keys($model->attributes)) . "')")
                ->queryAll();
        foreach ($settings as $setting) {
            $model->{$setting['name']} = $setting['value'];
        }

        $this->render('settings', array(
            'model' => $model,
            'ziplist' => unserialize($settings['zip_list']),
            'worktime'=> $worktime,
        ));
    }

    public function actionError() {
        if ($error = Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
                $this->render('error', $error);
        }
    }

}

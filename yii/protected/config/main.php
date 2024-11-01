<?php
return array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name' => 'WordPress Menu Maker',
    'sourceLanguage' => 'en_US',
    'preload' => array(
        'log',
    ),
    'import' => array(
        'application.models.*',
        'application.components.*',
        'application.extensions.CAdvancedArBehavior',
    ),
    'defaultController' => 'front',
    'components' => array(
        'Paypal' => array(
            'class'=>'application.components.Paypal',
            'apiUsername' => '',
            'apiPassword' => '',
            'apiSignature' => '',
            'apiLive' => true,

            'returnUrl' => 'paypal/confirm/', //regardless of url management component
            'cancelUrl' => 'paypal/cancel/', //regardless of url management component

            // Default currency to use, if not set USD is the default
            'currency' => 'USD',

            // Default description to use, defaults to an empty string
            //'defaultDescription' => '',

            // Default Quantity to use, defaults to 1
            //'defaultQuantity' => '1',

            //The version of the paypal api to use, defaults to '3.0' (review PayPal documentation to include a valid API version)
            //'version' => '3.0',
        ),
        'file' => array(
            'class' => 'application.extensions.CFile',
        ),
        'mail' => array(
             'class' => 'ext.YiiMail.YiiMail',
             'transportType' => 'php',
             'viewPath' => 'application.views.mail',
             'logging' => true,
             'dryRun' => false
         ),
        'user' => array(
            // enable cookie-based authentication
            'allowAutoLogin' => true,
        ),
        'request' => array(
            'class' => 'application.components.HttpRequest',
            'baseUrl' => MM_URL . "yii",
            'scriptUrl' => MM_PATH . "yii" . DIRECTORY_SEPARATOR . "index.php",
            'enableCsrfValidation' => true,
            'csrfTokenName' => 'WPMM_CSRF_TOKEN',
        ),
        'assetManager' => array(
            'basePath' => MM_PATH . "yii" . DIRECTORY_SEPARATOR . 'assets',
            'baseUrl' => MM_URL . "yii" . DIRECTORY_SEPARATOR . 'assets',
        ),
        'urlManager' => array(
            //'urlFormat'=>'path',
            'routeVar' => 'page',
        /*
          'rules'=>array(
          '<controller:\w+>/<id:\d+>'=>'<controller>/view',
          '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
          '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
          ),
         */
        ),
        'db' => array(
            'connectionString' => 'mysql:host=' . MM_DB_HOST . ';dbname=' . MM_DB_NAME,
            'emulatePrepare' => true,
            'username' => MM_DB_USER,
            'password' => MM_DB_PASS,
            'charset' => MM_DB_CHARSET,
        ),
        'session' => array(
            'class' => 'CDbHttpSession',
            'sessionName' => 'wpmm_session',
            'autoCreateSessionTable'=> true,
            'sessionTableName' => MM_DB_PREFIX . MM_TABLE_PREFIX . 'session',
            'connectionID' => 'db',
            'timeout' => 21600,
        ),
        'errorHandler' => array(
            'errorAction' => 'admin/error',
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'error, warning, info',
                ),
            ),
        ),
        'bootstrap' => array(
            'class' => 'ext.bootstrap.components.Bootstrap',
            'coreCss' => false,
            'yiiCss' => false,
            'jqueryCss' => false,
            'popoverSelector' => '.wpmm-popover',
            'tooltipSelector' => '.wpmm-tooltip',
        ),
    ),
    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params' => array(

    ),
);
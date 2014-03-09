<?php
/* @var $this DefaultController */

$this->breadcrumbs = array(
    $this->module->id,
);
?>
<h1><?php echo $this->uniqueId . '/' . $this->action->id; ?></h1>

<?php $this->renderPartial('_menu'); ?>

<?php $this->beginWidget('ext.yii-angularjs-helper.YiiAngularjsHelper', array(
        'appName'                    => 'testApp',
        'hasAppModule'               => true,
        'appFolder'                  => dirname(__FILE__) . '/../../angularjs/app1',
        'commonAppScripts'           => array(
            dirname(__FILE__) . '/../../angularjs/common/config_httpProvider.js',
            dirname(__FILE__) . '/../../angularjs/common/config_locationProvider.js',
        ),
        'appScripts'                 => array('test.js'),
        'appStyles'                  => array('style.css'),
        'requiredModulesScriptNames' => array('route', 'animate'),
        'customPlaceholders'         => array('[:DATETIME]' => date('Y-m-d H:i:s'), '[:CUSTOM_02]' => 'Value 2'),
        'scriptsPosition'            => CClientScript::POS_HEAD,
        'concatenateAppScripts'      => true,
        'debug'                      => true,
    )
); ?>

<nav>
    <ul class="inline-block-list" ng-click="showMobileMenu === true ? (showMobileMenu = false) : null">
        <li><a href="#!/">Home</a></li>
        <li><a href="#!/page1">Page 1</a></li>
        <li><a href="#!/page2">Page 2</a></li>
    </ul>
</nav>

<div data-ng-view class="view-animate"></div>


<?php $this->endWidget('ext.yii-angularjs-helper.YiiAngularjsHelper'); ?>

<?php
/* @var $this DefaultController */

$this->breadcrumbs = array(
    $this->module->id,
);
?>
<h1><?php echo $this->uniqueId . '/' . $this->action->id; ?></h1>

<?php $this->renderPartial('_menu'); ?>

<?php $this->beginWidget('ext.yii-angularjs-helper.YiiAngularjsHelper', array(
        'appName'                    => 'todoApp',
        'appFolder'                  => dirname(__FILE__) . '/../../angularjs/app2',
        'appScripts'                 => array('todo.js'),
        'commonAppScripts'           => array(
            dirname(__FILE__) . '/../../angularjs/common/config_httpProvider.js',
            dirname(__FILE__) . '/../../angularjs/common/config_locationProvider.js',
        ),
        'requiredModulesScriptNames' => array('route'),
        'concatenateAppScripts'      => false,
        'debug'                      => false,
    )
); ?>


<h2>Todo</h2>
<div ng-controller="TodoCtrl">
    <span>{{remaining()}} of {{todos.length}} remaining</span>
    [ <a href="" ng-click="archive()">archive</a> ]
    <ul class="unstyled">
        <li ng-repeat="todo in todos">
            <input type="checkbox" ng-model="todo.done">
            <span class="done-{{todo.done}}">{{todo.text}}</span>
        </li>
    </ul>
    <form ng-submit="addTodo()">
        <input type="text" ng-model="todoText" size="30"
               placeholder="add new todo here">
        <input class="btn-primary" type="submit" value="add">
    </form>
</div>


<?php $this->endWidget('ext.yii-angularjs-helper.YiiAngularjsHelper'); ?>

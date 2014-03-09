<?php
/* @var $this DefaultController */

$this->breadcrumbs = array(
    $this->module->id,
);
?>
<h1><?php echo $this->uniqueId . '/' . $this->action->id; ?></h1>

<?php $this->renderPartial('_menu'); ?>

<?php $this->widget('ext.yii-angularjs-helper.YiiAngularjsHelper', array(
        'includeAngular'        => false,
        'embedded'              => false,
        'appFolder'             => dirname(__FILE__) . '/../../angularjs/app2',
        'appScripts'            => array('todo.js'),
        'appName'               => 'dontInclude',
        'concatenateAppScripts' => false,
    )
); ?>


<div data-ng-app>
    <label>Name:</label>
    <input type="text" ng-model="yourName" placeholder="Enter a name here">

    <h1 style="margin: 1em 0;">Hello {{yourName}}!</h1>

    <p>
        This example shows how to use the YiiAngularjsHelper extension with an app that is using AngularJS globally, for
        example
        with the ng-app attribute set in the "html" tag and the angular minified script loaded from the CDN calling the
        script from the page &lt;head&gt;
    </p>

    <p>
        In this case it is still possible to take advantage of the extension by loading our scripts
        using the appFolder / appScripts parameters:
    </p>

    <h2>From the todo app:</h2>
    <div ng-controller="TodoCtrl">
        <ul class="unstyled">
            <li ng-repeat="todo in todos">
                <span class="done-{{todo.done}}">{{todo.text}} <span data-ng-show="todo.done">[done]</span></span>
            </li>
        </ul>
    </div>

</div>
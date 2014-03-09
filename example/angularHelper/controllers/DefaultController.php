<?php

class DefaultController extends Controller
{
    public function actionIndex() {
        $this->render('index');
    }

    public function actionTodoApp() {
        $this->render('todo');
    }

    public function actionDontInclude() {

        Yii::app()->getClientScript()->registerScriptFile('https://ajax.googleapis.com/ajax/libs/angularjs/1.2.14/angular.min.js', CClientScript::POS_HEAD);

        $this->render('dont_include');
    }

}
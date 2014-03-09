'use strict';


var testApp = angular.module('testApp', ['ngAnimate', 'ngRoute']);

//This configures the routes and associates each route with a view and a controller

testApp.config(function ($routeProvider, $locationProvider
    ) {

    $routeProvider.when('/', {
        templateUrl: '[:APP_ASSETS_URL]/partials/index.html'
    });

    $routeProvider.when('/page1', {
        templateUrl: '[:APP_ASSETS_URL]/partials/page1.html'
    });

    $routeProvider.when('/page2', {
        templateUrl: '[:APP_ASSETS_URL]/partials/page2.html'
    });

    $routeProvider.otherwise({ redirectTo: '/' });
});


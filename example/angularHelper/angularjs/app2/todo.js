
// the todoApp module is not really used, but it is here to show how it can be
// used with the common application scripts ($commonAppScripts)
var todoApp = angular.module('TodoApp', ['ngRoute']);


function TodoCtrl($scope) {

    $scope.yiiParams = {};
    setYiiParams($scope.yiiParams);

    $scope.todos = [
        {text:'learn angular', done:true},
        {text:$scope.yiiParams.todoText, done:$scope.yiiParams.todoDone},
        {text:'build an angular app', done:false}];

    $scope.addTodo = function() {
        $scope.todos.push({text:$scope.todoText, done:false});
        $scope.todoText = '';
    };

    $scope.remaining = function() {
        var count = 0;
        angular.forEach($scope.todos, function(todo) {
            count += todo.done ? 0 : 1;
        });
        return count;
    };

    $scope.archive = function() {
        var oldTodos = $scope.todos;
        $scope.todos = [];
        angular.forEach(oldTodos, function(todo) {
            if (!todo.done) $scope.todos.push(todo);
        });
    };
}
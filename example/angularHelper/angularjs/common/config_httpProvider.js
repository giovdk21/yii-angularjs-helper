
_PH__APP_NAME_.config(['$httpProvider', function($httpProvider) {
	$httpProvider.defaults.headers.common["FROM-ANGULAR"] = "true";
}]);
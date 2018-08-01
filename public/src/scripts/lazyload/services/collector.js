var collector_service = angular.module('app.service.collector', ['app.constants']);

collector_service.service('collectorService', ['$http', 'API_URL', function($http, API_URL)  {
    delete $http.defaults.headers.common['X-Requested-With'];

    this.index = function() {
        return $http.get(API_URL+'collector');
    };

    this.store = function(params) {
        return $http.post(API_URL+'collector', params);
    };

    this.update = function(params) {
        return $http.put(API_URL+'collector/' + params.id, params);
    };

    this.destroy = function(id) {
        return $http.delete(API_URL+'collector/' + id);
    };
}]);
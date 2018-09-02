var collector_service = angular.module('app.service.collector', ['app.constants']);

collector_service.service('collectorService', ['$http', 'API_URL', function($http, API_URL)  {
    delete $http.defaults.headers.common['X-Requested-With'];

    this.index = function () {
        return $http.get(API_URL+'listacobradores');
    };
    
    this.detail = function (id) {
        return $http.get(API_URL+'movil/listaclientes?idusuario=' + id);
    };
}]);
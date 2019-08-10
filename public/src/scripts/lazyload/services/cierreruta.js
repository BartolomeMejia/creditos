var cierre_ruta = angular.module('app.service.cierreruta', ['app.constants']);

cierre_ruta.service('cierreRutaService', ['$http', 'API_URL', function($http, API_URL)  {
    delete $http.defaults.headers.common['X-Requested-With'];

    this.cierreRutaList = function () {
        return $http.get(API_URL+'cierreruta');
    };

    this.validateCierreRuta = function (collectorId, date) {
        return $http.get(API_URL+'validatecierreruta?collector_id='+collectorId+'&date='+date);
    };

    this.saveClosingRoute = function(dataRouteClosure){
        return $http.post(API_URL+'cierreruta', dataRouteClosure);
    }
}]);
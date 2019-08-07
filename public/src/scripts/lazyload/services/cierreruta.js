var cierre_ruta = angular.module('app.service.cierreruta', ['app.constants']);

cierre_ruta.service('cierreRutaService', ['$http', 'API_URL', function($http, API_URL)  {
    delete $http.defaults.headers.common['X-Requested-With'];

    this.cierreRutaList = function () {
        return $http.get(API_URL+'cierreruta');
    };

    this.collectors = function () {
        return $http.get(API_URL+'listacobradores');
    };

    this.deleteHistory = function(id){
        return $http.get(API_URL+'deletepayment?detalle_id='+id);
    }
}]);
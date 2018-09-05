var pdfs_service = angular.module('app.service.pdfs', ['app.constants']);

pdfs_service.service('pdfsService', ['$http', 'API_URL', function($http, API_URL)  {
    delete $http.defaults.headers.common['X-Requested-With'];
    
    this.ticketcredit = function (id) {
        return $http.get(API_URL+'boletapdf?credit_id=' + id);
    };
}]);
var pdfs_service = angular.module('app.service.pdfs', ['app.constants']);

pdfs_service.service('pdfsService', ['$window','$http', 'API_URL', function($window, $http, API_URL)  {
    delete $http.defaults.headers.common['X-Requested-With'];
    
    this.ticketcredit = function (id) {
        console.log(API_URL+'boletapdf?credito_id=' + id);
        $window.location.href = API_URL+'boletapdf?credito_id=' + id;
    };
}]);
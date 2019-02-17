var report_service = angular.module('app.service.report', ['app.constants']);

report_service.service('reportService', ['$http', 'API_URL', function($http, API_URL)  {
    delete $http.defaults.headers.common['X-Requested-With'];

    this.general = function () {
        return $http.get(API_URL+'reportgeneral');
    };
    this.collector = function (collector, dateInit, dateFinal, plan, branch) {
        return $http.get(API_URL+'reportcollector?collector='+collector+'&date-init='+dateInit+'&date-final='+dateFinal+'&plan='+plan+'&branch='+branch);
    };
}]);
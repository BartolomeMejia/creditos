; (function () {
    "use strict";
  
    angular.module("app.reports", ["app.constants", 'app.service.collector', 'app.service.plan', 'app.service.pdfs'])
  
      .controller("ReportsController", ["$scope", "$filter", "$http", "$modal", "$interval", 'collectorService', 'planService', 'pdfsService', 'API_URL', function ($scope, $filter, $http, $modal, $timeout, collectorService, planService, pdfsService, API_URL) {

        $scope.collectors = []
        $scope.plans = []

        loadCollectors()
        loadPlanes()
        
        function loadCollectors(){
            collectorService.index().then(function (response) {
                response.data.records.forEach(function (item) {
                    if (item.sucursales_id == $scope.usuario.sucursales_id) {
                        $scope.collectors.push(item)
                    }
                })        
            });
        }

        function loadPlanes(){
            planService.plans().then(function (response) {
                response.data.records.forEach(function (item) {
                    if (item.sucursales_id == $scope.usuario.sucursales_id) {
                        $scope.plans.push(item)
                    }
                })        
            });
        }

      }])
  }())
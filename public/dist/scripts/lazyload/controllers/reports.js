; (function () {
    "use strict";
  
    angular.module("app.reports", ["app.constants", 'app.service.collector', 'app.service.plan', 'app.service.report', 'app.service.pdfs'])
  
      .controller("ReportsController", ["$scope", "$filter", "$http", "$modal", "$interval", 'collectorService', 'planService', 'reportService', 'pdfsService', 'API_URL', function ($scope, $filter, $http, $modal, $timeout, collectorService, planService, reportService, pdfsService, API_URL) {

        $scope.positionModel = "topRight";
        $scope.toasts = [];
        $scope.collectors = []
        $scope.plans = []
        $scope.customer = {}

        var dateToday =  $filter('date')(new Date(), 'yyyy-MM-dd')
        $("#date_init").val(dateToday);
        $("#date_fin").val(dateToday);

        loadCollectors()
        loadPlanes()        
        
        function loadCollectors(){
            collectorService.index().then(function (response) {
                response.data.records.forEach(function (item) {
                    if (item.sucursales_id == $scope.usuario.sucursales_id) {
                        $scope.collectors.push(item)
                    }
                })        
            })
        }

        function loadPlanes(){
            planService.plans().then(function (response) {
                response.data.records.forEach(function (item) {
                    if (item.sucursales_id == $scope.usuario.sucursales_id) {
                        $scope.plans.push(item)
                    }
                })        
            })
        }

        $scope.generateReport = function() {
            var $collector =  $scope.collector
            var $dateInit = $("#date_init").val()
            var $dateFinal = $("#date_fin").val()
            var $plan = $scope.plan == undefined ? "" : $scope.plan;
            var $branch = $scope.usuario.sucursales_id
            console.log($collector)
            console.log($dateInit)
            console.log($dateFinal)
            console.log($plan)
            console.log($branch)
            if ($collector != "" && $collector != undefined) {
                reportService.collector($collector, $dateInit, $dateFinal, $plan, $branch)
                    .then(function(response){
                        if (response.data.result) {
                            var info = response.data.records
                            $scope.customer.total = info.customers.withCredit
                            $scope.customer.today = info.customers.withCreditToDay
                            $scope.customer.notoday = info.customers.withCreditNoToDay
                            $scope.revenueTotals = info.revenueTotals
                            $scope.totalPendingReceivable = info.totalPendingReceivable
                            $scope.totalReceivable = info.totalReceivable
                        }
                    })
            } else {
                $scope.createToast("danger", "<strong>Error: Debe de seleccionar un cobrador. </strong>");
                $timeout(function () { $scope.closeAlert(0); }, 5000);
            }
        }

         // toast function
        $scope.createToast = function (tipo, mensaje) {
            $scope.toasts.push({
                anim: "bouncyflip",
                type: tipo,
                msg: mensaje
            });
        }

        $scope.closeAlert = function (index) {
            $scope.toasts.splice(index, 1);
        }

    }])
}())
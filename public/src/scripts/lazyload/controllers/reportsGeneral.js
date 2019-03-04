; (function () {
    "use strict";
  
    angular.module("app.reports", ["app.constants", 'app.service.report', 'app.service.pdfs'])
  
      .controller("ReportsController", ["$scope", "$filter", "$http", "$modal", "$interval", 'reportService', 'pdfsService', 'API_URL', function ($scope, $filter, $http, $modal, $timeout, reportService, pdfsService, API_URL) {

        $scope.positionModel = "topRight";
        $scope.toasts = [];    
        $scope.customerGeneral = {}
        var typeView = "";

        loadGeneralReport()    
        

        function loadGeneralReport(){        
            var branch = $scope.usuario.sucursales_id            

            reportService.general(branch)
                .then(function(response){
                    if (response.data.result) {
                        var info = response.data.records                         
                        if (info != null && info != "" ) {
                            $scope.customerGeneral.total = info.customers.withCredit
                            $scope.customerGeneral.today = info.customers.withCreditToDay
                            $scope.customerGeneral.notoday = info.customers.withCreditNoToDay
                            $scope.customerGeneral.revenueTotals = info.revenueTotals
                            $scope.customerGeneral.totalPendingReceivable = info.totalPendingReceivable
                            $scope.customerGeneral.totalReceivable = info.totalReceivable                           
                        } else {
                            $scope.createToast("danger", "<strong>Error: No hay datos a mostrar. </strong>");
                        $timeout(function () { $scope.closeAlert(0); }, 5000);
                        }
                    } else {
                        $scope.createToast("danger", "<strong>Error: Ocurrió un error al consultar los datos. </strong>");
                        $timeout(function () { $scope.closeAlert(0); }, 5000);
                    }
            })
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
            $scope.toasts.splice(index, 1)
        }

    }])
}())
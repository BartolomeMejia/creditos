;(function()
{
    "use strict";

    angular.module("app.abonos", ["app.constants"])

        .controller("AbonosController", ["$scope", "$routeParams", "$filter", "$http", "$modal", "$interval", "API_URL", function($scope, $routeParams, $filter, $http, $modal, $timeout, API_URL)  {

            $scope.positionModel = "topRight";
            $scope.detalle_cliente = {};
            $scope.search_client = {};
            $scope.credito = {};
            $scope.toasts = [];
            $scope.resumen = {};
            var modal;

            $scope.validarCliente = function(search_client){
                $http({
                    method: 'GET',
                    url:    API_URL+'buscarcliente',
                    params: search_client
                })
                    .then(function successCallback(response) {
                            if (response.data.result) {
                                $('.row-detalle').removeClass('hidden');
                                $scope.detalle_cliente = response.data.records;
                                $scope.detalle_cliente.nombre = response.data.records.nombre+' '+response.data.records.apellido;
                                $scope.detalle_cliente.cuota_diaria = response.data.records.creditos.cuota_diaria;
                                $scope.credito.total = "Q. "+parseFloat(response.data.records.creditos.deudatotal).toFixed(2);
                                $scope.credito.saldo = "Q. "+parseFloat(response.data.records.creditos.saldo).toFixed(2);
                                $scope.credito.saldo_abonado = "Q. "+parseFloat(response.data.records.creditos.saldo_abonado).toFixed(2);


                                $scope.createToast("success", "<strong>Éxito: </strong>"+response.data.message);
                                $timeout( function(){ $scope.closeAlert(0); }, 5000);
                            }
                            else {
                                console.log(response.data)
                                $scope.createToast("danger", "<strong>Error: </strong>"+response.data.message);
                                $timeout( function(){ $scope.closeAlert(0); }, 5000);
                            }
                        },
                        function errorCallback(response) {
                            console.log(response);
                        });
            };

            if($routeParams.id){
                $scope.search_client.dpi = parseInt($routeParams.id);
                
                $scope.validarCliente($scope.search_client);
            }

            $scope.createToast = function(tipo, mensaje) {
                $scope.toasts.push({
                    anim: "bouncyflip",
                    type: tipo,
                    msg: mensaje
                });
            }

            $scope.closeAlert = function(index) {
                $scope.toasts.splice(index, 1);
            };


            

            $scope.modalcuotas = function() {
                $scope.resumen.cantidadabonada = $scope.cantidad_ingresada;
                $scope.resumen.cantidadcuotas = parseInt($scope.cantidad_ingresada / $scope.detalle_cliente.cuota_diaria);
                $scope.resumen.abonocapital = $scope.cantidad_ingresada - ($scope.resumen.cantidadcuotas * $scope.detalle_cliente.cuota_diaria);
                
                modal = $modal.open({
                    templateUrl: "views/abonos/modal.html",
                    scope: $scope,
                    size: "md",
                    resolve: function() {},
                    windowClass: "default"
                });
            }
            $scope.modalClose = function() {
                modal.close();
            }
            
            $scope.registrarAbono = function (cantidadAbonada) {

                var datos = {
                    idcredito:$scope.detalle_cliente.creditos.id,
                    abono:cantidadAbonada
                };

                $http({
                    method: 'POST',
                    url: 	API_URL+'registrarabonos',
                    data: 	datos
                })
                .then(function successCallback(response) {
                    if( response.data.result ) {
                        
                        modal.close();
                        $scope.createToast("success", "<strong>Éxito: </strong>"+response.data.message);
                        /*$timeout( function(){ 
                            $scope.closeAlert(0); 
                        }, 5000);*/
                        window.location = "#/abonos";
                    }
                    else {
                        modal.close();
                        $scope.createToast("danger", "<strong>Error: </strong>"+response.data.message);
                        $timeout( function(){ $scope.closeAlert(0); }, 5000);
                    }
                },
                function errorCallback(response) {
                    console.log( response.data.message );
                });
            }
        }])
}())
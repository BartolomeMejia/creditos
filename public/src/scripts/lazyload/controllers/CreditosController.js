;(function() 
{
	"use strict";

	angular.module("app.creditos", ["app.constants"])

	.controller("CreditosController", ["$scope", "$filter", "$http", "$modal", "$interval", "API_URL", function($scope, $filter, $http, $modal, $timeout, API_URL)  {	
		$scope.positionModel = "topRight";
		$scope.detalle_cliente = {};
		$scope.toasts = [];
        var modal;

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



        $scope.cargarPlanes = function() {
            $http.get(API_URL+'planes', {}).then(function(response) {
                if (response.data.result) {
                    $scope.planes = response.data.records;
                }
            });
        };

        $scope.cargarMonto = function() {
            $http.get(API_URL+'montosprestamo', {}).then(function(response) {
                if (response.data.result)
                    $scope.montosprestamo = response.data.records;
            });
        };

        $scope.cargarUsuariosCobrador = function() {
        	console.log("prueba");
            $http.get(API_URL+'listacobradores', {}).then(function(response) {
                if (response.data.result)
                	console.log(response.data.records);
                    $scope.usuarios_cobrador = response.data.records;
            });
        };

        $scope.calcularInteresCuota = function( plan ) {
			$scope.detalle_cliente.interes = ($scope.detalle_cliente.monto_id.monto * plan.porcentaje) / 100;
            $scope.detalle_cliente.cuota_diaria = ($scope.detalle_cliente.interes + $scope.detalle_cliente.monto_id.monto) / plan.dias;
        };


        $scope.cargarPlanes();
        $scope.cargarMonto();
        $scope.cargarUsuariosCobrador();

		$scope.validarCliente = function(search_client){
			$http({
				method: 'GET',
			  	url: 	API_URL+'buscarcliente',
			  	params: search_client
			})
			.then(function successCallback(response) {
				if (response.data.result) {
					$('#row-detalle').removeClass('hidden');
					$scope.detalle_cliente = response.data.records;
					$scope.detalle_cliente.nombre = response.data.records.nombre+' '+response.data.records.apellido;
					
					// Obtiene y da formato a la fecha de inicio
					var fecha_inicio = $filter('date')(new Date(),'dd-MM-yyyy');
					$scope.detalle_cliente.fecha_inicio = fecha_inicio;

					// Calcula y da formato a la fecha fin
					var fecha_fin = new Date();
					var numberOfDaysToAdd = 1;
					fecha_fin.setDate(fecha_fin.getDate() + numberOfDaysToAdd); 
				  	$scope.detalle_cliente.fecha_fin = $filter('date')(fecha_fin,'dd-MM-yyyy');

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

		$scope.saveData = function( detalleCredito ) {

				var datos = {
					idcliente:detalleCredito.id,
					idplan:detalleCredito.planes_id.id,
					idmonto:detalleCredito.monto_id.id,
					idusuario:detalleCredito.usuarios_cobrador.id,
					deudatotal:(detalleCredito.monto_id.monto + detalleCredito.interes),
					cuota_diaria:detalleCredito.cuota_diaria,
					cuota_minima:(detalleCredito.monto_id.monto / detalleCredito.planes_id.dias),
					fecha_inicio:detalleCredito.fecha_inicio,
					fecha_limite:detalleCredito.fecha_fin
				};
				console.log(datos);
				$http({
					method: 'POST',
				  	url: 	API_URL+'creditos',
				  	data: 	datos
				})
				.then(function successCallback(response) {
					if( response.data.result ) {

					    $scope.createToast("success", "<strong>Éxito: </strong>"+response.data.message);
					    $timeout( function(){ $scope.closeAlert(0); }, 5000);
					}
					else {
						$scope.createToast("danger", "<strong>Error: </strong>"+response.data.message);
					    $timeout( function(){ $scope.closeAlert(0); }, 5000);	
					}
				}, 
				function errorCallback(response) {
				   console.log( response.data.message );
				});

		}

        $scope.saveDataNewClient = function( cliente ) {
            if ($scope.accion == 'crear') {
                $http({
                    method: 'POST',
                    url: API_URL + 'clientes',
                    data: cliente
                })
                    .then(function successCallback(response) {
                            if (response.data.result) {

                                $('#row-detalle').removeClass('hidden');

                                $scope.detalle_cliente = response.data.records;
                                $scope.detalle_cliente.nombre = response.data.records.nombre+' '+response.data.records.apellido;

                                // Obtiene y da formato a la fecha de inicio
                                var fecha_inicio = $filter('date')(new Date(),'dd-MM-yyyy');
                                $scope.detalle_cliente.fecha_inicio = fecha_inicio;

                                // Calcula y da formato a la fecha fin
                                var fecha_fin = new Date();
                                var numberOfDaysToAdd = 1;
                                fecha_fin.setDate(fecha_fin.getDate() + numberOfDaysToAdd);
                                $scope.detalle_cliente.fecha_fin = $filter('date')(fecha_fin,'dd-MM-yyyy');

                                modal.close();
                                $scope.createToast("success", "<strong>Éxito: </strong>" + response.data.message);
                                $timeout(function () {
                                    $scope.closeAlert(0);
                                }, 5000);
                            }
                            else {
                                $scope.createToast("danger", "<strong>Error: </strong>" + response.data.message);
                                $timeout(function () {
                                    $scope.closeAlert(0);
                                }, 5000);
                            }
                        },
                        function errorCallback(response) {
                            console.log(response.data.message);
                        });
            }
        }

        // Funciones para Modales
        $scope.modalCreateOpen = function() {
            $scope.cliente = {};
            $scope.accion = 'crear';

            modal = $modal.open({
                templateUrl: "views/creditos/modal.html",
                scope: $scope,
                size: "md",
                resolve: function() {},
                windowClass: "default"
            });
        }

        $scope.modalClose = function() {
            modal.close();
        }
	}])
}())
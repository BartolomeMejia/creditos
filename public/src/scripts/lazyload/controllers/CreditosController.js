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
                    $scope.usuarios_cobrador = response.data.records.filter(x => x.sucursales_id == $scope.usuario.sucursales_id);
            });
        };

        $scope.calcularInteresCuota = function( plan ) {
			$scope.detalle_cliente.interes = ($scope.detalle_cliente.monto_id.monto * plan.porcentaje) / 100;
            $scope.detalle_cliente.cuota_diaria = ($scope.detalle_cliente.interes + $scope.detalle_cliente.monto_id.monto) / plan.dias;
        };

		$scope.getEndDate = function( plan ){
			var startDate = $("#fechainicio").val().split("-")
			var endDate = new Date(startDate[2], startDate[1] - 1, startDate[0])
			endDate.setDate(endDate.getDate() + plan.dias);
			$scope.detalle_cliente.fecha_fin = $filter('date')(endDate,'dd-MM-yyyy');
		}

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

				    $scope.createToast("success", "<strong>Éxito: </strong>"+response.data.message);
				    $timeout( function(){ $scope.closeAlert(0); }, 5000);
				}
				else {
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
				
				$http({
					method: 'POST',
				  	url: 	API_URL+'creditos',
				  	data: 	datos
				})
				.then(function successCallback(response) {
					if( response.data.result ) {

						$scope.createToast("success", "<strong>Éxito: </strong>"+response.data.message);
						$('#row-detalle').addClass('hidden');
						$('#customerDpi').val("");
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
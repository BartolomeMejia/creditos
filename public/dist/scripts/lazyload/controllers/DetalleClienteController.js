;(function() 
{
	"use strict";

	angular.module("app.detallecliente", ["app.constants"])

	.controller("DetalleClienteController", ["$scope", "$routeParams", "$filter", "$http", "$modal", "$interval", "API_URL", function($scope, $routeParams, $filter, $http, $modal, $timeout, API_URL)  {	
		
		var customer = {};
		$scope.listCredito = [];
		$scope.itemCredit = "";
		$scope.showInputSelect = false

		$scope.datosCliente = function(id)
		{
			$http({
				method: 'GET',
			  	url: 	API_URL+'detallecliente',
			  	params: {cliente_id:id}
			})
			.then(function successCallback(response)  {						
				customer =  response.data.records

				if (customer.creditos.length > 1){
					$scope.showInputSelect = true
					arrayCredits(customer.creditos)
				}
				showData(customer.creditos[0])
			}, 
			function errorCallback(response)  {			
			   console.log( response.data.message );
			});
		}

		$scope.datosCliente($routeParams.id);
		$scope.creditSelected = function(credit){
			showData(credit)
		}

		function showData(infoCredit){			
			$scope.dpi	= customer.dpi
			$scope.nombre = customer.nombre
			$scope.apellido = customer.apellido
			$scope.nombre_completo = customer.nombre+' '+customer.apellido
			$scope.sexo = customer.sexo == 1 ? "Masculino" : "Femenino"
			$scope.direccion = customer.direccion
			$scope.estado_civil = customer.estado_civil == 1 ? "Soltero (a)" : "Casado (a)"
			$scope.telefono = customer.telefono

			$scope.plan = infoCredit.planes.descripcion
			$scope.monto_total = "Q. "+parseFloat(infoCredit.deudatotal).toFixed(2);
			$scope.fecha_inicio = infoCredit.fecha_inicio
			$scope.fecha_fin = infoCredit.fecha_fin		
			$scope.cobrador = infoCredit.usuariocobrador.nombre
			$scope.cuota_diaria = "Q. "+parseFloat(infoCredit.cuota_diaria).toFixed(2);
				
			$scope.saldo_pendiente = "Q. "+parseFloat(infoCredit.saldo).toFixed(2);
			$scope.total_cancelado = "Q. "+parseFloat(infoCredit.total_cancelado).toFixed(2);
			$scope.cuotas_pagadas = infoCredit.cuotas_pagados;

			$scope.porcentaje = parseInt(infoCredit.porcentaje_pago);
		}

		function arrayCredits(credits){
			credits.forEach(function (item) {            	
				$scope.listCredito.push(item)                
			})

			$scope.itemCredit = $scope.listCredito[0]
		}
	}])
}())
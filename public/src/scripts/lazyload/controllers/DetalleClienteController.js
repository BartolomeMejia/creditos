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
				console.log(response.data.records)
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
			$scope.nombre = customer.nombre
			$scope.apellido = customer.apellido
			$scope.dpi	= customer.dpi
			$scope.plan = infoCredit.planes.descripcion
			$scope.nombre_completo = customer.nombre+' '+customer.apellido
			$scope.fecha_inicio = infoCredit.fecha_inicio
			$scope.cobrador = infoCredit.usuariocobrador.nombre
			$scope.fecha_fin = infoCredit.fecha_fin			
			$scope.total = "Q. "+parseFloat(infoCredit.deudatotal).toFixed(2);
			$scope.saldo = "Q. "+parseFloat(infoCredit.saldo).toFixed(2);
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
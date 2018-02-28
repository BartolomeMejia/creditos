;(function() 
{
	"use strict";

	angular.module("app.detallecliente", ["app.constants"])

	.controller("DetalleClienteController", ["$scope", "$routeParams", "$filter", "$http", "$modal", "$interval", "API_URL", function($scope, $routeParams, $filter, $http, $modal, $timeout, API_URL)  {	
		//Variables generales

		$scope.cliente = {};


		$scope.datosCliente = function(id)
		{
			$http({
				method: 'GET',
			  	url: 	API_URL+'detallecliente',
			  	params: {cliente_id:id}
			})
			.then(function successCallback(response)  {
			    $scope.cliente = response.data.records;
			    $scope.cliente.nombrecompleto = $scope.cliente.cliente.nombre+' '+$scope.cliente.cliente.apellido;
			    $scope.cliente.total = "Q. "+parseFloat($scope.cliente.deudatotal).toFixed(2);
                $scope.cliente.saldo = "Q. "+parseFloat($scope.cliente.saldo).toFixed(2);
                $scope.cliente.porcentaje = parseInt((parseInt($scope.cliente.cuotasPagadas) * 100)/parseInt($scope.cliente.cuotasPendientes));
                console.log($scope.cliente.porcentaje);
			}, 
			function errorCallback(response)  {			
			   console.log( response.data.message );
			});
		}

		$scope.datosCliente($routeParams.id);
	}])
}())
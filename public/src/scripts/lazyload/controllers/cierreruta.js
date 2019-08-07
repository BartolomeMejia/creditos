;(function() 
{
	"use strict";

	angular.module("app.cierreRuta", ["app.constants", 'app.service.cierreruta'])

	.controller("CierreRutaController", ["$scope", "$filter", "$modal", "$interval", 'cierreRutaService', 'API_URL', function($scope, $filter, $modal, $timeout, cierreRutaService, API_URL)  {	
		
		$scope.datas = Array();
		$scope.date = "";

		$scope.currentPageStores = [];
		$scope.searchKeywords = "";
		$scope.filteredData = [];	
		$scope.row = "";
		$scope.numPerPageOpts = [5, 10, 25, 50, 100];
		$scope.numPerPage = $scope.numPerPageOpts[1];
		$scope.currentPage = 1;
		$scope.positionModel = "topRight";
		$scope.toasts = [];
		var modal;
		var showPanelPayments = false;
		var collectorSelected = {};
		
		showCierreRutas();
		
		
		function showCierreRutas() {
			cierreRutaService.cierreRutaList()
				.then(function successCallback(response) {        
					console.log(response.data.records)        
                    $scope.datas = response.data.records;
					$scope.search();
					$scope.select($scope.currentPage);
			  	})
		}


		//FUNCIONES DE TOAST		
		$scope.createToast = function(tipo, mensaje) {
			$scope.toasts.push({
				anim: "bouncyflip",
				type: tipo,
				msg: mensaje
			});
		}

		$scope.closeAlert = function(index) {
			$scope.toasts.splice(index, 1);
		}

		// FUNCIONES DE DATATABLE
		$scope.select = function(page) {
			var start = (page - 1)*$scope.numPerPage,
				end = start + $scope.numPerPage;

			$scope.currentPageStores = $scope.filteredData.slice(start, end);
		}

		$scope.onFilterChange = function() {
			$scope.select(1);
			$scope.currentPage = 1;
			$scope.row = '';
		}

		$scope.onNumPerPageChange = function() {
			$scope.select(1);
			$scope.currentPage = 1;
		}

		$scope.onOrderChange = function() {
			$scope.select(1);
			$scope.currentPage = 1;
		}

		$scope.search = function() {
			$scope.filteredData = $filter("filter")($scope.datas, $scope.searchKeywords);
			$scope.onFilterChange();		
		}

		$scope.order = function(rowName) {
			if($scope.row == rowName)
				return;
			$scope.row = rowName;
			$scope.filteredData = $filter('orderBy')($scope.datas, rowName);
			$scope.onOrderChange();
		}	

		//FUNCIONES DE MODALES
		$scope.modalConfirm = function(data) {			
			$scope.accion = 'confirmar';
			$scope.record = data;

			modal = $modal.open({
				templateUrl: "views/cierreruta/modal.html",
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
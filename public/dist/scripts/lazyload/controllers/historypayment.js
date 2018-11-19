;(function() 
{
	"use strict";

	angular.module("app.historypayment", ["app.constants", 'app.service.historypayment'])

	.controller("HistoryPaymentController", ["$scope", "$filter", "$http", "$modal", "$interval", 'historyPaymentService', "API_URL", function($scope, $filter, $http, $modal, $timeout, historyPaymentService, API_URL)  {	
		
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

		$scope.existhistory = function(){
			return showPanelPayments;
		}
	
		loadCollectors();
		
		function loadCollectors() {
			
			$scope.collectors = [];

			historyPaymentService.collectors()
				.then(function successCallback(response) {
			  		response.data.records.forEach(function (item) {
					if (item.sucursales_id == $scope.usuario.sucursales_id) {
				  		$scope.collectors.push(item)
					}
			  	})
			});
		}

		function loadPayments(collectorId, selectedDate) {
			historyPaymentService.historylist(collectorId, selectedDate)
				.then(function successCallback(response){
					console.log(response.data);					
					$scope.datas = response.data.records;
					$scope.search();
					$scope.select($scope.currentPage);
				})
		}

		$scope.selectCollector = function (collector) {
			showPanelPayments = true;
			collectorSelected = collector;
		}

		$scope.findRecords = function(){
			if($("#fechainicio").val() != ""){
				var selectedDate = $("#fechainicio").val()
				var collectorId = collectorSelected.id
				loadPayments(collectorId, selectedDate)
			}
		}

		$scope.saveData = function( record ) {
			if ($scope.accion == 'eliminar') {	
				historyPaymentService.deleteHistory(record.id, record.fecha_pago)			
					.then(function successCallback(response) {
						if( response.data.result ) {
							
							var selectedDate = $("#fechainicio").val()
							var collectorId = collectorSelected.id
							loadPayments(collectorId, selectedDate)
							
							modal.close();
							$scope.createToast("success", "<strong>Éxito: </strong>"+response.data.message);
							$timeout( function(){ $scope.closeAlert(0); }, 3000);
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
		$scope.modalDeleteOpen = function(data) {			
			$scope.accion = 'eliminar';
			$scope.record = data;

			modal = $modal.open({
				templateUrl: "views/sucursales/modal.html",
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
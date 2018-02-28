;(function() {
	"use strict";

	angular.module("app.usuarios", [])

	.controller("UsuariosController", ["$scope", "$filter", "$http", function($scope, $filter, $http) {
		
		$scope.datas = [];

		$http.get("../ws/usuarios", {}).then(function(dataResponse){
			$scope.datas = dataResponse.data.records;
			$scope.currentPageStores = dataResponse.data.records;
		});


		// FUNCIONES PARA DATATABLE
		var prelength = $scope.datas.length;

		$scope.searchKeywords = "";
		$scope.filteredData = [];	
		$scope.row = "";

		$scope.numPerPageOpts = [5, 10, 25, 50, 100];
		$scope.numPerPage = $scope.numPerPageOpts[1];
		$scope.currentPage = 1;
		$scope.currentPageStores = []; 

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

		$scope.search();
		$scope.select($scope.currentPage);
	}])

	.controller("ModalUsuariosController", ["$scope", "$modal", function($scope, $modal) {

		$scope.modalAnim = "default";

		$scope.modalCreateOpen = function() {
			$modal.open({
				templateUrl: "views/usuarios/modal.html",
				size: "md",
				controller: "ModalUsuariosController",
				resolve: function() {},
				windowClass: $scope.modalAnim
			});
			
		}

		$scope.modalClose = function() {
			$scope.$close();
		}
	}])
}())
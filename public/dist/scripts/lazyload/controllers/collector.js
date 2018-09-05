; (function () {
  "use strict";

  angular.module("app.collector", ["app.constants", 'app.service.collector'])

    .controller("CollectorController", ["$scope", "$filter", "$http", "$modal", "$interval", 'collectorService', 'API_URL', function ($scope, $filter, $http, $modal, $timeout, collectorService, API_URL) {

      // general vars
      $scope.loadBranches = [];
      $scope.datas = Array();
      $scope.sucursales = Array();
      $scope.currentPageStores = [];
      $scope.searchKeywords = ''
      $scope.filteredData = [];
      $scope.row = '';
      $scope.numPerPageOpts = [5, 10, 25, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpts[1];
      $scope.currentPage = 1;
      $scope.positionModel = 'topRight';
      $scope.toasts = [];
      $scope.showCollectorTable = true;
      $scope.collectorSelected = '';
      $scope.totalCobrar = 0;
      $scope.totalMinimoCobrar = 0;
      $scope.totalCartera = 0;
      var modal;
      var pivotStructure = [];


      function loadBranches() {
        $http.get(API_URL + 'sucursales', {})
          .then(function successCallback(response) {
            if (response.data.result) {
              if ($scope.usuario.tipo_usuarios_id == 1)
                $scope.sucursales = response.data.records;
              else {
                // $scope.sucursales = response.data.records.filter(x => x.id == $scope.usuario.sucursales_id)
                response.data.records.forEach(function (item) {
                  if (item.id == $scope.usuario.sucursales_id) {
                    $scope.sucursales.push(item)
                  }
                })
              }
            }
          });
      }

      function loadData(branch_id) {

        var branch_selectd = branch_id != null ? branch_id : $scope.usuario.sucursales_id;

        collectorService.index().then(function (response) {
          // $scope.datas = response.data.records.filter(x => x.sucursales_id == branch_selectd)
          response.data.records.forEach(function (item) {
            if (item.sucursales_id == branch_selectd) {
              $scope.sucursales.push(item)
            }
          })
          $scope.search();
          $scope.select($scope.currentPage);
        });
      }

      // datatable collector functions
      $scope.select = function (page) {
        var start = (page - 1) * $scope.numPerPage,
          end = start + $scope.numPerPage;

        $scope.currentPageStores = $scope.filteredData.slice(start, end);
      }

      $scope.onFilterChange = function () {
        $scope.select(1);
        $scope.currentPage = 1;
        $scope.row = '';
      }

      $scope.onNumPerPageChange = function () {
        $scope.select(1);
        $scope.currentPage = 1;
      }

      $scope.onOrderChange = function () {
        $scope.select(1);
        $scope.currentPage = 1;
      }

      $scope.search = function () {
        $scope.filteredData = $filter("filter")($scope.datas, $scope.searchKeywords);
        $scope.onFilterChange();
      }

      $scope.order = function (rowName) {
        if ($scope.row == rowName)
          return;
        $scope.row = rowName;
        $scope.filteredData = $filter('orderBy')($scope.datas, rowName);
        $scope.onOrderChange();
      }

      loadBranches();
      loadData($("branch_id").val());

      $scope.changeDataBranch = function(branch_id){
        loadData(branch_id);
      }

      $scope.showCustomerView = function(data){
        collectorService.detail(data.id).then(function(response){
          $scope.collectorSelected = data.nombre;
          $scope.showCollectorTable = false;

          $scope.totalCobrar = response.data.records.total_cobrar;
          $scope.totalMinimoCobrar = response.data.records.total_minimo;

          response.data.records.registros.forEach(function (element) {
            const recordDate = new Date(element.updated_at)
            const currentDate = new Date()

            const recorDateParsed = recordDate.getDate() + '-' + recordDate.getMonth() + '-' + recordDate.getFullYear()
            const currentDateParsed = currentDate.getDate() + '-' + currentDate.getMonth() + '-' + currentDate.getFullYear()

            if (recorDateParsed === currentDateParsed) {
              element.updated_at = 1
            } else {
              element.updated_at = 0
            }

            $scope.totalCartera = $scope.totalCartera + element.deudatotal
          });

          pivotStructure = $scope.datas;
          $scope.datas = [];
          $scope.datas = response.data.records.registros;
          $scope.searchKeywords = '';
          $scope.search();
          $scope.select($scope.currentPage);
        });
      }

      $scope.closeCustomerView = function(){
        $scope.showCollectorTable = true;
        $scope.datas = [];
        $scope.datas = pivotStructure;
        $scope.searchKeywords = '';
        $scope.search();
        $scope.select($scope.currentPage);
        $scope.totalCobrar = 0;
        $scope.totalMinimoCobrar = 0;
        $scope.totalCartera = 0;
      }

      // modals function
      $scope.modalCreateOpen = function(){
        $scope.usuario = {};
        $scope.accion = 'crear';

        modal = $modal.open({
          templateUrl: "views/usuarios/modal.html",
          scope: $scope,
          size: "md",
          resolve: function () { },
          windowClass: "default"
        });
      }

      $scope.modalEditOpen = function(data){
        $scope.accion = 'editar';
        $scope.usuario = data;

        data.estado == 1 ? $scope.usuario.estado = true : $scope.usuario.estado = false;

        modal = $modal.open({
          templateUrl: "views/usuarios/modal.html",
          scope: $scope,
          size: "md",
          resolve: function () { },
          windowClass: "default"
        });
      }

      $scope.modalDeleteOpen = function (data) {
        $scope.accion = 'eliminar';

        $scope.usuario = data;
        modal = $modal.open({
          templateUrl: "views/usuarios/modal.html",
          scope: $scope,
          size: "md",
          resolve: function () { },
          windowClass: "default"
        });
      }

      $scope.modalClose = function () {
        modal.close();
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
        $scope.toasts.splice(index, 1);
      }
    }])
}())
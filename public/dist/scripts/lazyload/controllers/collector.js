; (function () {
  "use strict";

  angular.module("app.collector", ["app.constants", 'app.service.collector', 'app.service.pdfs'])

    .controller("CollectorController", ["$scope", "$filter", "$http", "$modal", "$interval", 'collectorService', 'pdfsService', 'API_URL', function ($scope, $filter, $http, $modal, $timeout, collectorService, pdfsService, API_URL) {

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
      $scope.totalPendientePago = 0;
      var modal;
      var pivotStructure = []
      var collectorSelected = {}

      var dateToday =  $filter('date')(new Date(), 'yyyy-MM-dd')
      $("#fechapago").val(dateToday);
      loadBranches();
      loadData($("branch_id").val());

      function loadBranches() {
        $scope.sucursales = [];
        $http.get(API_URL + 'sucursales', {})
          .then(function successCallback(response) {
            if (response.data.result) {
              if ($scope.usuario.tipo_usuarios_id == 1)
                $scope.sucursales = response.data.records;
              else {              
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
        $scope.datas = [];
        collectorService.index().then(function (response) {
          response.data.records.forEach(function (item) {
            if (item.sucursales_id == branch_selectd) {
              $scope.datas.push(item)
            }
          })
          pivotStructure = $scope.datas;
          $scope.search();
          $scope.select($scope.currentPage);
        });
      }

      function totalCollection(cobradorId, date){
        collectorService.totalColletion(cobradorId, date)
          .then(function successCallback(response){     
            $scope.collectionofday = response.data.records;       					          
          },
          function errorCallback(response) {
            $scope.collectionofday = 0;						
					});
      }

      function showCustomer(data){
        var date = $("#fechapago").val()
        collectorService.detail(data.id, date).then(function(response){          
          $scope.collectorSelected = data.nombre          
          $scope.showCollectorTable = false

          $scope.totalCobrar = response.data.records.total_cobrar
          $scope.totalMinimoCobrar = response.data.records.total_minimo

          var collectionofday = 0;
          response.data.records.registros.forEach(function (element) {
            $scope.totalCartera = $scope.totalCartera + element.deudatotal
            $scope.totalPendientePago = $scope.totalPendientePago + element.saldo
          });

          totalCollection(data.id, date)
          
          $scope.datas = []
          $scope.datas = response.data.records.registros
          $scope.searchKeywords = ''
          $scope.search()
          $scope.select($scope.currentPage)
        });
      }

      $scope.changeDataBranch = function(branch_id){
        loadData(branch_id);
      }

      $scope.findCustomers = function(){
        if($("#fechapago").val() != ""){
          var selectedDate = $("#fechapago").val()
          var collectorId = collectorSelected
          $scope.totalCartera = 0
          $scope.totalPendientePago = 0
          showCustomer(collectorId)
        }
      }
      
      $scope.showCustomerView = function(data){
        showCustomer(data)
        collectorSelected = data
        pivotStructure = $scope.datas
      }

      $scope.closeCustomerView = function(){
        $scope.showCollectorTable = true
        $scope.datas = []
        $scope.datas = pivotStructure
        $scope.searchKeywords = ''
        $scope.search()
        $scope.select($scope.currentPage)
        $scope.totalCobrar = 0
        $scope.totalMinimoCobrar = 0
        $scope.totalCartera = 0
        $scope.totalPendientePago = 0
      }

      $scope.printResume = function(){
        if($("#fechapago").val() != ""){
          pdfsService.resumenPaymentCollector(collectorSelected.id, $("#fechapago").val())
        }
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
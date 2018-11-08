; (function () {
  "use strict";

  angular.module("app.abonos", ["app.constants", 'app.service.customers'])

    .controller("AbonosController", ["$scope", "$routeParams", "$filter", "$http", "$modal", "$interval", 'customersService', "API_URL", function ($scope, $routeParams, $filter, $http, $modal, $timeout, customersService, API_URL) {

      $scope.positionModel = "topRight";
      $scope.detalle_cliente = {};
      $scope.search_client = {};
      $scope.credito = {};
      $scope.toasts = [];
      $scope.resumen = {};
      $scope.dailyFee = 0;
      var modal;
      var nameCustomer = "";
      var lastNameCustomer = "";

      $("#customerName").focus();
      loadCustomers()

      function loadCustomers() {
        customersService.customers().then(function (response) {
          $scope.customers = response.data.records
        });
      }

      $("#customerName").change('input', function () {
        var opt = $('option[value="' + $(this).val() + '"]');
        nameCustomer = opt.length ? opt.attr('data-name') : "";
        lastNameCustomer = opt.length ? opt.attr('data-lastname') : "";

      })

      $scope.validarCliente = function (search_client) {
        if (nameCustomer != "" && lastNameCustomer != "") {
          $http({
            method: 'GET',
            url: API_URL + 'creditocliente',
            params: { name: nameCustomer, lastname: lastNameCustomer }
          }).then(function successCallback(response) {
            if (response.data.result) {
              $('.row-detalle').removeClass('hidden');
              $scope.detalle_cliente = response.data.records;
              $scope.detalle_cliente.nombre = response.data.records.nombre + ' ' + response.data.records.apellido;
              $scope.detalle_cliente.cuota_diaria = "Q. " + parseFloat(response.data.records.creditos.cuota_diaria).toFixed(2);
              $scope.credito.total = "Q. " + parseFloat(response.data.records.creditos.deudatotal).toFixed(2);
              $scope.credito.saldo = "Q. " + parseFloat(response.data.records.creditos.saldo).toFixed(2);
              $scope.credito.saldo_abonado = "Q. " + parseFloat(response.data.records.creditos.saldo_abonado).toFixed(2);
              $scope.dailyFee = response.data.records.creditos.cuota_diaria;

              $scope.createToast("success", "<strong>Éxito: </strong>" + response.data.message);
              $timeout(function () { $scope.closeAlert(0); }, 5000);
            }
            else {
              $scope.createToast("danger", "<strong>Error: </strong>" + response.data.message);
              $timeout(function () { $scope.closeAlert(0); }, 5000);
            }
          }, function errorCallback(response) {

          });
        } else {
          $scope.createToast("danger", "<strong>Error: </strong> El nombre del cliente es incorrecto");
          $timeout(function () { $scope.closeAlert(0); }, 5000);
        }
      };

      if ($routeParams.id) {
        $scope.search_client.dpi = parseInt($routeParams.id);
        $scope.validarCliente($scope.search_client);
      }

      $scope.createToast = function (tipo, mensaje) {
        $scope.toasts.push({
          anim: "bouncyflip",
          type: tipo,
          msg: mensaje
        });
      }

      $scope.closeAlert = function (index) {
        $scope.toasts.splice(index, 1);
      };

      $scope.modalcuotas = function (cantidadAbonada) {

        if (cantidadAbonada != '' && parseFloat(cantidadAbonada) > 0) {
          $scope.resumen.cantidadabonada = $scope.cantidad_ingresada;
          $scope.resumen.cantidadcuotas = parseInt($scope.cantidad_ingresada / $scope.dailyFee);
          $scope.resumen.abonocapital = $scope.cantidad_ingresada - ($scope.resumen.cantidadcuotas * $scope.dailyFee);
          console.log('abono ', $scope.detalle_cliente);
          modal = $modal.open({
            templateUrl: "views/abonos/modal.html",
            scope: $scope,
            size: "md",
            resolve: function () { },
            windowClass: "default"
          });
        } else {
          $scope.createToast("danger", "<strong>Error: </strong>" + 'Debe ingresar una cantidad válida');
        }
      }

      $scope.modalClose = function () {
        modal.close();
      }

      $scope.registrarAbono = function (cantidadAbonada) {
        if (cantidadAbonada != '' && parseFloat(cantidadAbonada) > 0) {
          var datos = {
            idcredito: $scope.detalle_cliente.creditos.id,
            abono: cantidadAbonada
          };
          console.log(datos);
          $http({
            method: 'POST',
            url: API_URL + 'registrarabonos',
            data: datos
          }).then(function successCallback(response) {
            if (response.data.result) {
              modal.close();
              $scope.createToast("success", "<strong>Éxito: </strong>" + response.data.message);
              window.location = "#/abonos";
            } else {
              modal.close();
              $scope.createToast("danger", "<strong>Error: </strong>" + response.data.message);
              $timeout(function () { $scope.closeAlert(0); }, 5000);
            }
          }, function errorCallback(response) {
            console.log(response.data.message);
          });
        } else {
          $scope.createToast("danger", "<strong>Error: </strong>" + 'Debe ingresar una cantidad válida');
        }
      }
    }])
}())
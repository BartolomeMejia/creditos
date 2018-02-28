;(function() 
{
	"use strict";

	angular.module("app", [
		/* Angular modules */
		"ngRoute",
		"ngAnimate",
		"ngSanitize",
		"ngAria",
		"ngMaterial",

		/* 3rd party modules */
		"oc.lazyLoad",
		"ui.bootstrap",
		"angular-loading-bar",
		"FBAngular",
	
		/* custom modules */
		"app.ctrls",
		"app.directives",
		"app.ui.ctrls",
		"app.ui.directives",
		"app.form.ctrls",
		"app.table.ctrls",
		"app.email.ctrls",
		"app.todo"
	])

	// disable spinner in loading-bar
	.config(['cfpLoadingBarProvider', function(cfpLoadingBarProvider) {
	    cfpLoadingBarProvider.includeSpinner = false;
	     cfpLoadingBarProvider.latencyThreshold = 500;
	}])

	// lazy loading scripts refernces of angular modules only
	.config(["$ocLazyLoadProvider", function($oc) {
		$oc.config({
			debug: true,
			event: false,
			modules: [{
					name: "angularBootstrapNavTree",
					files: ["scripts/lazyload/abn_tree_directive.js", "styles/lazyload/abn_tree.css"]
				},
				{
					name: "ui.calendar",
					serie: true,	// load files in series
					files: [
						"scripts/lazyload/moment.min.js", 
						"scripts/lazyload/fullcalendar.min.js",  
						"styles/lazyload/fullcalendar.css",  
						"scripts/lazyload/calendar.js"
					]
				},
				{
					name: "ui.select",
					files: ["scripts/lazyload/select.min.js", "styles/lazyload/select.css"]
				},
				{
					name: "ngTagsInput",
					files: ["scripts/lazyload/ng-tags-input.min.js", "styles/lazyload/ng-tags-input.css"]
				},
				{
					name: "colorpicker.module",
					files: ["scripts/lazyload/bootstrap-colorpicker-module.min.js", "styles/lazyload/colorpicker.css"]
				},
				{
					name: "ui.slider",
					serie: true,
					files: ["scripts/lazyload/bootstrap-slider.min.js", "scripts/lazyload/directives/bootstrap-slider.directive.js", "styles/lazyload/bootstrap-slider.css"]
				},
				{
					name: "textAngular",
					serie: true,
					files: ["scripts/lazyload/textAngular-rangy.min.js",  "scripts/lazyload/textAngular.min.js", "scripts/lazyload/textAngularSetup.js", "styles/lazyload/textAngular.css"]
				},
				{
					name: "flow",
					files: ["scripts/lazyload/ng-flow-standalone.min.js"]
				},
				{
					name: "ngImgCrop",
					files: ["scripts/lazyload/ng-img-crop.js", "styles/lazyload/ng-img-crop.css"]
				},
				{
					name: "ngMask",
					files: ["scripts/lazyload/ngMask.min.js"]
				},
				{
					name: "angular-c3",
					files: ["scripts/lazyload/directives/c3.directive.js"]
				},
				{
					name: "easypiechart",
					files: ["scripts/lazyload/angular.easypiechart.min.js"]
				},
				{
					name: "ngMap",
					files: ["scripts/lazyload/ng-map.min.js"]
				}
			]
		})
	}])

	// jquery/javascript and css for plugins via lazy load
	.constant("JQ_LOAD", {
		fullcalendar: [],
		moment: ["scripts/lazyload/moment.min.js"],
		sparkline: ["scripts/lazyload/jquery.sparkline.min.js"],
		c3: ["scripts/lazyload/d3.min.js", "scripts/lazyload/c3.min.js", "styles/lazyload/c3.css"],
		gmaps: ["https://maps.google.com/maps/api/js"]
	})

	// route provider
	.config(["$routeProvider", "$locationProvider", "JQ_LOAD", function($routeProvider, $locationProvider, jqload) {

		var routes = [];

		function setRoutes(route) {
			var url = '/' + route,
				config = {
					templateUrl: "views/" + route + ".html"
				};

			$routeProvider.when(url, config);
			return $routeProvider;
		}

		routes.forEach(function(route) {
			setRoutes(route);
		});

		$routeProvider
			.when("/", {redirectTo: "/dashboard"})
			.when("/404", {templateUrl: "views/pages/404.html"})
			.otherwise({redirectTo: "/404"});

		$routeProvider.when("/dashboard", {
			templateUrl: "views/dashboard.html",
			resolve: {
				deps: ["$ocLazyLoad", function(a) {
					return a.load([jqload.c3, jqload.sparkline])
					.then(function() {
						return a.load({
							name: "app.directives",
							files: ["scripts/lazyload/directives/sparkline.directive.js"]
						})
					})
					.then(function() {
						return a.load("angular-c3");
					})
					.then(function() {
						return a.load("easypiechart");
					})
				}]
			}
		});

		$routeProvider.when("/creditos", {
			templateUrl: "views/creditos/creditos.html",
			controller: "CreditosController",
			resolve: {
				deps: ["$ocLazyLoad", function(a) {
					return a.load({
						name: "app.creditos",
						files: ["scripts/controllers/CreditosController.js"]
					})
				}]
			}
		});

        $routeProvider.when("/abonos", {
            templateUrl: "views/abonos/abonos.html",
            controller: "AbonosController",
            resolve: {
                deps: ["$ocLazyLoad", function(a) {
                    return a.load({
                        name: "app.abonos",
                        files: ["scripts/controllers/AbonosController.js"]
                    })
                }]
            }
        });
        
        $routeProvider.when("/abonos/:id", {
            templateUrl: "views/abonos/abonos.html",
            controller: "AbonosController",
            resolve: {
                deps: ["$ocLazyLoad", function(a) {
                    return a.load({
                        name: "app.abonos",
                        files: ["scripts/controllers/AbonosController.js"]
                    })
                }]
            }
        });
		
		$routeProvider.when("/usuarios", {
			templateUrl: "views/usuarios/usuarios.html",
			controller: "UsuariosController",
			resolve: {
				deps: ["$ocLazyLoad", function(a) {
					return a.load({
						name: "app.usuarios",
						files: ["scripts/controllers/UsuariosController.js"]
					})
				}]
			}
		});

		$routeProvider.when("/clientes", {
			templateUrl: "views/clientes/clientes.html",
			controller: "ClientesController",
			resolve: {
				deps: ["$ocLazyLoad", function(a) {
					return a.load({
						name: "app.clientes",
						files: ["scripts/controllers/ClientesController.js"]
					})
				}]
			}
		});

		$routeProvider.when("/sucursales", {
			templateUrl: "views/sucursales/sucursales.html",
			controller: "SucursalesController",
			resolve: {
				deps: ["$ocLazyLoad", function(a) {
					return a.load({
						name: "app.sucursales",
						files: ["scripts/controllers/SucursalesController.js"]
					})
				}]
			}
		});

		$routeProvider.when("/planes", {
			templateUrl: "views/planes/planes.html",
			controller: "PlanesController",
			resolve: {
				deps: ["$ocLazyLoad", function(a) {
					return a.load({
						name: "app.planes",
						files: ["scripts/controllers/PlanesController.js"]
					})
				}]
			}
		});

		$routeProvider.when("/montos", {
			templateUrl: "views/montos/montos.html",
			controller: "MontosController",
			resolve: {
				deps: ["$ocLazyLoad", function(a) {
					return a.load({
						name: "app.montos",
						files: ["scripts/controllers/MontosController.js"]
					})
				}]
			}
		});

		$routeProvider.when("/detallecliente/:id", {
			templateUrl: "views/clientes/detallecliente.html",
			controller: "DetalleClienteController",
			resolve: {
				deps: ["$ocLazyLoad", function(a) {
					return a.load({
						name: "app.detallecliente",
						files: ["scripts/controllers/DetalleClienteController.js"]
					})
				}]
			}
		});
	}])
}())
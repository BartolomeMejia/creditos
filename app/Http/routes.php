<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return redirect('src');
});

Route::get('/boletaview', function(){
	return view('pdf.boleta');
});

Route::group(['prefix' => 'ws'], function() {
	Route::resource('tipousuarios', 		'TipoUsuariosController');
	Route::resource('sucursales', 			'SucursalesController');
	Route::resource('planes', 				'PlanesController');
	Route::resource('montosprestamo',		'MontosPrestamoController');
	Route::resource('clientes',				'ClientesController');
	Route::resource('referenciasclientes',	'ReferenciasPersonalesClientesController');
	Route::resource('creditos',				'CreditosController');
	Route::resource('usuarios',				'UsuariosController');
	Route::any('insertar/envases',			'EnvasesController@insertarEnvaseNuevo');
	Route::post('login',					'UsuariosController@login');

	Route::get('logout',function() {
		Auth::logout();
		return \Redirect::to('/');
	});

	Route::get('cobradorclientes',			'CreditosController@cobradorClientes');
	Route::get('listacobradores',			'UsuariosController@listacobradores');
	Route::post('registrarabonos',			'CreditosController@registrarAbono');
	Route::get('buscarcliente',				'ClientesController@buscarCliente');
	Route::get('detallecliente',			'ClientesController@detalleCreditoCliente');
	Route::get('boletapdf',					'CreditosController@boletaPDF');
});

Route::group(['prefix' => 'ws/movil'], function()
{	
	Route::any('login',				'CobradorMovilController@loginMovil');
	Route::any('listaclientes',		'CobradorMovilController@listadoClientesCobrador');
});

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Usuarios;
use App\Creditos;
use App\CuotasClientes;
use App\CreditosDetalle;
use Auth;
use DB;
use Session;

//revisar el usuario que no sea repedito
//validar el password que vaya vacio

class CobradorMovilController extends Controller
{
    public $statusCode  = 200;
    public $result      = false;
    public $message     = "";
    public $records     = [];
    
    public function loginMovil (Request $request)
    {
        try{
            
            $usuario = Usuarios::where("user", $request->input("user"))->first();
            if( $usuario ){
                if( \Hash::check($request->input("password"),  $usuario->password)){
                    $this->result   = true;
                    $this->message  = "Bienvenido";
                    $this->records  = $usuario;
                }
                else
                    throw new \Exception("Datos incorrectos, intenta de nuevo"); 
            }
            else
                throw new \Exception("El email ingresado no esta registrado"); 

        } 
        catch (\Exception $e) 
        {
            $this->result       =   false;
            $this->message      =  env('APP_DEBUG')?$e->getMessage():'Ocurrio un problema al procesar la solicitud';
        }
        finally{
            $response = 
            [
                'message'   =>  $this->message,
                'result'    =>  $this->result,
                'records'   =>  $this->records
            ];
        }
        return response()->json($response, $this->statusCode);        
    }

    public function listadoClientesCobrador(Request $request)
    {
        try {

            $registros = Creditos::where("usuarios_cobrador", $request->input("idusuario"))->where("estado",1)->with("cliente")->get();

            if( $registros ){

                $totalacobrar = 0;
                $totalminimocobrar = 0;
                $cantidadclientes = 0;

                foreach ($registros as $item) {
                    $detalleCreditos    = CreditosDetalle::where('creditos_id', $item->id)->get();

                    if( $detalleCreditos ){
                        
                        $cantidadCuotasPagadas = 0;
                        $montoAbono = 0;
                        
                        foreach ($detalleCreditos as $detalle) {   
                            if ($detalle->estado == 1)
                                $cantidadCuotasPagadas = $cantidadCuotasPagadas + 1;
                            else
                                $montoAbono = $detalle->abono;
                        }
                        $item['cantidad_cuotas_pagadas'] =  $cantidadCuotasPagadas ;
                        $item['monto_abonado'] = $montoAbono;
                    }
                    else{
                        $item['cantidad_abonos_realizados'] = 0;
                        $item['monto_abonado'] = 0;
                    }

                    $item['fecha_inicio'] = \Carbon\Carbon::parse($item->fecha_inicio)->format('d-m-Y');
                    $item['fecha_limite'] = \Carbon\Carbon::parse($item->fecha_limite)->format('d-m-Y');
                    $totalacobrar = $totalacobrar + $item->cuota_diaria;
                    $totalminimocobrar = $totalminimocobrar + $item->cuota_minima;
                    $cantidadclientes = $cantidadclientes + 1;
                }

                $datos = [];
                $datos['total_cobrar'] = $totalacobrar;
                $datos['total_minimo'] = $totalminimocobrar;
                $datos['registros'] = $registros;

                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registros consultados exitosamente";
                $this->records      = $datos;
            }
            else
                throw new \Exception("No se encontraron registros");
                
        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar los registros";
        }
        finally{
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];

            return response()->json($response, $this->statusCode);
        }
    }
} 


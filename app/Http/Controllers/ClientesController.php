<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Clientes;
use App\Creditos;
use App\DetallePagos;
use App\Usuarios;
use App\Http\Traits\detailsPaymentsTrait;
use App\Http\Traits\detailsCreditsTrait;

class ClientesController extends Controller {
    public $statusCode  = 200;
    public $result      = false;
    public $message     = "";
    public $records     = [];
    protected $sessionKey = 'usuario';
    
    use detailsPaymentsTrait;
    use detailsCreditsTrait;

    public function index() {
        try {
            $registros = Clientes::all();
            
            if ($registros) {
                $registros->map(function ($item, $key){   
                    if($item->status == 1){
                        $detailCredits = $this->getStatusCredits($item->id);
                        $item->statusCredit = $detailCredits->status;
                        $item->totalCredits = $detailCredits->total;
                        $item->collector = $detailCredits->collector;
                    } else {
                        $item->statusCredit = 4;
                        $item->totalCredits = 0;
                        $item->collector = 0;
                    }
                    return $item;
                });

                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registros consultados exitosamente";
                $this->records      = $registros;
            } else
                throw new \Exception("No se encontraron registros");
        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar los registros";
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];
            return response()->json($response, $this->statusCode);
        }
    }

    public function create(){}
    
    public function store(Request $request){
        try {
            $nuevoRegistro = \DB::transaction( function() use ($request) {
                                $nuevoRegistro = Clientes::create([
                                                    'sucursal_id'   => $request->session()->get($this->sessionKey)->sucursales_id,
                                                    'nombre'        => $request->input('nombre'),
                                                    'apellido'      => $request->input('apellido'),
                                                    'dpi'           => $request->input('dpi'),
                                                    'telefono'      => $request->input('telefono'),
                                                    'direccion'      => $request->input('direccion'),
                                                    'estado_civil'  => $request->input('estado_civil'),
                                                    'sexo'          => $request->input('sexo'),
                                                    'categoria'     => 'A',
                                                    'color'         => 'verde',
                                                ]);

                                if( !$nuevoRegistro )
                                    throw new \Exception("No se pudo crear el registro");
                                else
                                    return $nuevoRegistro;
                            });

            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro creado exitosamente";
            $this->records      = $nuevoRegistro;

        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al crear el registro";
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

    public function show($id){
        try {
            $registro = Clientes::find( $id );

            if( $registro ){
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultado exitosamente";
                $this->records      = $registro;
            }
            else
                throw new \Exception("No se encontró el registro");
                
        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar el registro";
        }
        finally
        {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];

            return response()->json($response, $this->statusCode);
        }
    }

    public function edit($id){}
    
    public function update(Request $request, $id)
    {
        try {
            \DB::beginTransaction();
            $registro = Clientes::find( $id );
            $registro->nombre       = $request->input('nombre', $registro->nombre);
            $registro->apellido     = $request->input('apellido', $registro->apellido);
            $registro->dpi          = $request->input('dpi', $registro->dpi);
            $registro->telefono     = $request->input('telefono', $registro->telefono);
            $registro->direccion     = $request->input('direccion', $registro->direccion);
            $registro->estado_civil = $request->input('estado_civil', $registro->estado_civil);
            $registro->sexo         = $request->input('sexo', $registro->sexo);
            $registro->categoria    = $request->input('categoria', $registro->categoria);
            $registro->color        = $request->input('color', $registro->color);
            
            $credit = Creditos::where("clientes_id", $id)->get();
            
            if($credit->count() > 0){
                $credit->map(function ($item, $key) use ($request){   
                    $item->usuarios_cobrador = $request->input('collector', $item->usuarios_cobrador);
                    $item->save();
                    return $item;
                });
            } else {
                throw new \Exception("Error al editar el cliente");
            }

            if($registro->save()){
                \DB::commit();
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro editado exitosamente";
                $this->records      = $registro;
            } else {
                throw new \Exception("Error al editar el cliente");
            }
                
        } catch (\Exception $e) {
            \DB::rollback();
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al editar el registro";
        }
        finally
        {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];

            return response()->json($response, $this->statusCode);
        }
    }

    
    public function destroy($id)
    {
        try {
            $credit = Creditos::where("clientes_id", $id)->get();

            $deleteRegistro = \DB::transaction( function() use ( $id ){
                                $credit = Creditos::where('clientes_id', $id)->where('estado', 1)->get();

                                if($credit->count() > 0){
                                    $credit->map(function ($item, $key){   
                                        $item->estado = 2;
                                        $item->save();
                                        return $item;
                                    });
                                }

                                $registro = Clientes::find( $id );
                                $registro->status = 2;
                                $registro->save();

                            });

            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro eliminado exitosamente";
            
        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al eliminar el registro";
        }
        finally
        {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
            ];

            return response()->json($response, $this->statusCode);
        }
    }

    public function buscarCliente(Request $request)
    {
        try {
            $cliente = Clientes::where('dpi', $request->input('dpi') )->where('sucursal_id', $request->session()->get('usuario')->sucursales_id)->first();
            
            if($cliente){

                $credito = Creditos::where("clientes_id", $cliente->id)->first();

                if(!$credito){
                    $this->statusCode   = 200;
                    $this->result       = true;
                    $this->message      = "Registro consultado exitosamente";
                    $this->records      = $cliente;
                }
                else{
                    throw new \Exception("El cliente ingresado ya cuenta con crédito");
                }
            }
            else {
                throw new \Exception("Cliente no encontrado");
            }   
        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar el registro";
        }
        finally
        {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];

            return response()->json($response, $this->statusCode);
        }
    }

    public function buscarCreditoCliente(Request $request){
        try{            
            $creditoCliente = Clientes::where('nombre', $request->input('name'))
                                        ->where('apellido', $request->input('lastname'))                                        
                                        ->where('sucursal_id', $request->session()->get('usuario')->sucursales_id)
                                        ->with('creditos')
                                        ->first();
        
            if($creditoCliente){
                if($creditoCliente->creditos && $creditoCliente->creditos->estado == 1){        
                    $detailsPayments = $this->getDetailsPayments($creditoCliente->creditos->id);                                    
                    $creditoCliente->creditos->saldo_abonado = $detailsPayments->paymentPaid;
                    $creditoCliente->creditos->cuotas_pagados = $detailsPayments->totalFees;
                    $creditoCliente->creditos->total_cancelado = $detailsPayments->totalPayment;
                    $this->statusCode   = 200;
                    $this->result       = true;
                    $this->message      = "Registro consultado exitosamente";
                    $this->records      = $creditoCliente;
                } else{
                    throw new \Exception("Cliente no cuenta con crédito");      
                }
            } else{
                throw new \Exception("No cliente ingresado no existe");  
            }
        }
        catch (\Exception $e){
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar el registro";
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

    public function detalleCreditoCliente(Request $request){
        try {
            $registro = Creditos::where('clientes_id', $request->input('cliente_id'))->with('cliente','planes','montos','usuariocobrador')->first();
            
            if ( $registro ) {
                $registro->porcentaje_pago = $this->getDetailsPayments($registro->id)->paymentPercentage;
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultado exitosamente";
                $this->records      = $registro;
            }
            else
                throw new \Exception("Error al consultar el registro");
                
        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar el registro";
        }
        finally
        {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];

            return response()->json($response, $this->statusCode);
        }
    }

    public function customersByBranch(Request $request){
        try{
            $customers = Clientes::with('creditos')   
                                ->whereHas('creditos', function($credit){
                                    $credit->where('estado', 1);
                                })
                                ->where('sucursal_id',$request->session()->get('usuario')->sucursales_id)
                                ->get();

            if($customers){
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultado exitosamente";
                $this->records      = $customers;
            }
        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar el registro";
        }
        finally
        {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];

            return response()->json($response, $this->statusCode);
        }
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Clientes;
use App\Creditos;
use App\CreditosDetalle;

class ClientesController extends Controller {
    public $statusCode  = 200;
    public $result      = false;
    public $message     = "";
    public $records     = [];
    protected $sessionKey = 'usuario';

    public function index() {
        try {
            $registros = Clientes::with('referenciasPersonales','creditos')->get();

            if ($registros) {
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

    
    public function create()
    {
        //
    }

    
    public function store(Request $request)
    {
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

    public function show($id)
    {
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

    
    public function edit($id)
    {
        //
    }

    
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
            $registro->save();

            \DB::commit();
            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro editado exitosamente";
            $this->records      = $registro;
                
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
            $deleteRegistro = \DB::transaction( function() use ( $id ){
                                $registro = Clientes::find( $id );
                                $registro->delete();
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
            
                $creditoCliente = Clientes::where('nombre', $request->input('name'))->where('apellido', $request->input('lastname'))->where('sucursal_id', $request->session()->get('usuario')->sucursales_id)->with('creditos')->first();
            
                if($creditoCliente){
                    
                    if($creditoCliente->creditos){        
                        $ultimoAbono = CreditosDetalle::where('creditos_id', $creditoCliente->creditos->id)->orderBy('id', 'desc')->first();
                        $abono = 0;

                        if( $ultimoAbono ){
                            if($ultimoAbono->estado == 0)
                                $abono = $ultimoAbono->abono; 
                        }

                        $creditoCliente->creditos->saldo_abonado = $abono;
                        $this->statusCode   = 200;
                        $this->result       = true;
                        $this->message      = "Registro consultado exitosamente";
                        $this->records      = $creditoCliente;
                    }
                    else{
                        throw new \Exception("Cliente no cuenta con crédito");      
                    }
                }
                else{
                    throw new \Exception("Cliente ingresado no cuenta con crédito");  
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
            $registro = Creditos::where('clientes_id', $request->input('cliente_id'))->with('cliente','planes','montos','usuariocobrador','detalleCreditos')->first();
            if ( $registro ) {
                $cuotas = 0;

                foreach ($registro->detalleCreditos as $valRegistro) {

                    if( $valRegistro['estado'] == 1 )
                    {
                        $cuotas = $cuotas + 1;
                    }
                }
                $registro->cuotasPagadas = $cuotas;
                $registro->cuotasPendientes = $registro->planes->dias - $registro->cuotas;


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
            $customers = Clientes::where('sucursal_id',$request->session()->get('usuario')->sucursales_id)->get();

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
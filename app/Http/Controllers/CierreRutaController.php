<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\CierreRuta;

class CierreRutaController extends Controller
{
    private $statusCode = 200;
    private $result = false;
    private $message = '';
    private $records = [];

    public function index(){
        try {
            //$registros = CierreRuta::where('sucursal_id', $request->input('sucursal'))->get();
            $registros = CierreRuta::with('cobrador')->get();

            if ( $registros ) {
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultados exitosamente";
                $this->records      = $registros;
            } else
                throw new \Exception("No se encontraron registros");
                
        } catch (\Exception $e) {
            $this->statusCode = 200;
            $this->result = false;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar los datos";
            
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];

            return response()->json($response, $this->statusCode);
        }
    }

    public function store(Request $request) {
        try {
            $nuevoRegistro = \DB::transaction(function() use ($request) {
                                $nuevoRegistro = CierreRuta::create([
                                    'sucursal_id' => $request->input('branch_id'),
                                    'cobrador_id' => $request->input('collector_id'),
                                    'monto_cierre' => $request->input('total_amount'),
                                    'fecha_cierre' => $request->input('date'),
                                    'fecha_cerrado' => \Carbon\Carbon::now()->toDateString(),   
                                    'hora' => \Carbon\Carbon::now()->toTimeString(),
                                    'estado' => 1
                                ]);

                                if ( !$nuevoRegistro) 
                                    throw new \Exception("Ocurrió un problema al realizar el cierre de la ruta. Por favor inténtelo nuevamente");
                                else
                                    return $nuevoRegistro;
                            });

            $this->statusCode   =   200;
            $this->result       =   true;
            $this->message      =   "Cierre de ruta generado exitosamente";
            $this->records      =   $nuevoRegistro;
            
        } catch (\Exception $e) {
            $this->statusCode   =   200;
            $this->result       =   false;
            $this->message      =   env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al realizar el cierre de la ruta. Por favor inténtelo nuevamente";
        } finally {
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
            $registro = CierreRuta::find($id);

            if ($registro) {
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultado exitosamente";
                $this->records      = $registro;
            } else
                throw new \Exception("No se encontro el registro");
                    
        } catch (\Exception $e) {
            $this->statusCode = 200;
            $this->result = false;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar el registro";
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];

            return response()->json($response, $this->statusCode);
        }
    }

    public function update(Request $request, $id)
    {
        try 
        {
            \DB::beginTransaction();
            $registro = CierreRuta::find( $id );
            /*$registro->fecha = $request->input('descripcion', $registro->descripcion);
            'fecha' => \Carbon\Carbon::now()->toDateString();   
            'hora' => \Carbon\Carbon::now()->toDateString();  */
            $registro->save();

            \DB::commit();
            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro actualizado exitosamente";
            $this->records      = $registro;
        } catch (\Exception $e) {
            \DB::rollback();
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al editar el registro";   
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];
            return response()->json($response, $this->statusCode);
        }
    }

    public function destroy($id) {
        try {
            $deleteRegistro = \DB::transaction(function() use( $id ){
                            $registro = CierreRuta::find( $id );
                            $registro->delete();
                        });

            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "El registro fue eliminado exitosamente";
            
        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al eliminar el registro";
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
            ];
            return response()->json($response, $this->statusCode);
        }
    }

    public function validateCierreRuta(Request $request) {
        try {
            $record = CierreRuta::where('cobrador_id', $request->input('collector_id'))
                                ->where('fecha_cierre', $request->input('date'))
                                ->where('estado', 1)
                                ->first();
            
            if ($record) {
                $this->result       = true;
                $this->message      = "Registro consultado exitosamente";
                $this->records      = true;
            } else
                throw new \Exception("No se encontro el registro");
                                    
        } catch (\Exception $e) {
            $this->records = false;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al obtener el registro";
        } finally {
            $response = [
                'result' => $this->result,
                'message' => $this->message,
                'records'   => $this->records
            ];

            return response()->json($response, $this->statusCode);
        }
    }
}

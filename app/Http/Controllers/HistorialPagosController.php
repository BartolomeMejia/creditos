<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use App\DetallePagos;
use App\Clientes;
use App\Creditos;
use DB;

class HistorialPagosController extends Controller
{
    public $statusCode = 200;
    public $result = false;
    public $message = '';
    public $records = [];

    public function paymentHistory(Request $request)
    {
        try {
            $items = DB::table('detalle_pagos')
                        ->join('creditos', 'detalle_pagos.credito_id','=','creditos.id')
                        ->where('creditos.usuarios_cobrador',$request->input('cobrador_id'))
                        ->where('detalle_pagos.fecha_pago',$request->input('fecha_pago'))                        
                        ->where('detalle_pagos.estado', 1)
                        ->get();

            if($items){
                $colletion = collect($items);
                $colletion->map(function ($item, $key){
                    return $item->customer = Clientes::find($item->clientes_id);
                });

                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultados exitosamente";
                $this->records      = $items;
            }
            else
                throw new \Exception("No se encontraron registros");
                
        } 
        catch (\Exception $e) {
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

    public function deletePayment(Request $request){
        try{

            $detallePago = DetallePagos::where('id', $request->input('detalle_id'))
                                        ->where('estado', 1)
                                        ->first();
            
            if($detallePago){
                
                $detallePago->estado = 2;
                
                if($detallePago->save()){                    
                    $credito = Creditos::find($detallePago->credito_id);
                    $credito->saldo = $credito->saldo + $detallePago->abono;
                    
                    if($credito->save()){
                        $this->statusCode   = 200;
                        $this->result       = true;
                        $this->message      = "Cobro eliminado correctamente";
                    } else {
                        throw new \Exception("Error al eliminar el pago");        
                    }
                }else {
                    throw new \Exception("Error al eliminar el pago");
                }    
            } else {
                throw new \Exception("No se encontró el pago a eliminar");
            }
        }
        catch (\Exception $e) {
            $this->statusCode = 200;
            $this->result = false;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar los datos";
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

    public function totalColletion(Request $request){
        try {            
            $items = DB::table('detalle_pagos')
                        ->join('creditos', 'detalle_pagos.credito_id','=','creditos.id')
                        ->where('creditos.usuarios_cobrador',$request->input('cobrador_id'))
                        //->where('historial_pagos.fecha_pago',$request->input('fecha_pago'))
                        ->where('detalle_pagos.fecha_pago',date('Y-m-d'))                        
                        ->where('detalle_pagos.estado', 1)
                        ->get();
            
            if($items){
                $colletion = collect($items);            

                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultados exitosamente";
                $this->records      = $colletion->sum('abono');
            }
            else
                throw new \Exception("No se encontraron registros");
                
        } 
        catch (\Exception $e) {
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
}

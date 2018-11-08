<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use App\HistorialPagos;
use App\Clientes;
use App\CreditosDetalle;
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
            $items = DB::table('historial_pagos')
                        ->join('creditos', 'historial_pagos.credito_id','=','creditos.id')
                        ->where('creditos.usuarios_cobrador',$request->input('cobrador_id'))
                        ->where('historial_pagos.fecha_pago',$request->input('fecha_pago'))
                        ->where('historial_pagos.tipo', 1)
                        ->where('historial_pagos.estado', 1)
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
            $history = HistorialPagos::where('credito_id', $request->input('credito_id'))
                                ->where('fecha_pago', $request->input('fecha_pago'))
                                ->where('estado', 1)
                                ->get();

            if($history){
            
                $colletion = collect($history);
                $colletion->map(function ($item, $key){
                    if($item->tipo == 2){                       
                        $creditoDetalle = CreditosDetalle::find($item->detalle_id);                                
                        $creditoDetalle->estado = 0;
                        $creditoDetalle->abono = $item->monto;
                        $creditoDetalle->save();
                        
                        $item->estado = 3;
                        $item->save();
                    } else {
                        $deleteHistory =  CreditosDetalle::where('creditos_id', $item->credito_id)
                                                ->where('fecha_pago', $item->fecha_pago)
                                                ->get();
                                            
                        if($deleteHistory->count() > 0){
                            $colletion = collect($deleteHistory);
                            $colletion->map(function ($item, $key){
                                return $item->delete();
                            });
                        }                                            
                        
                        $creditos = Creditos::find($item->credito_id);
                        $creditos->saldo = $creditos->saldo + $item->monto;
                        $creditos->save();
                        
                        $item->estado = 3;
                        $item->save();
                    }                    
                });
                
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Cobro eliminado correctamente";
                
            } else {
                throw new \Exception("No se encontraron registros");
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
}

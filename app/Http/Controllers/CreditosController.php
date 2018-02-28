<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Creditos;
use App\CreditosDetalle;
use App\CuotasClientes;
use App\Planes;
use Session;

class CreditosController extends Controller
{
    public $statusCode  = 200;
    public $result      = false;
    public $message     = "";
    public $records     = [];

    public function index()
    {
        try {
            $registros = Creditos::with('cliente','planes','montos','usuariocobrador','detalleCreditos')->get();

            if( $registros ) 
            {
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registros consultados exitosamente";
                $this->records      = $registros;  
            }
            else
                throw new \Exception("No se encontraron registros");
                
            
        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;  
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar los registros"; 
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

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        try {

            $plan = Planes::find($request->input('idplan'));

            $fecha_fin = strtotime ( '+'.$plan->dias.' day', strtotime ( $request->input('fecha_inicio') ));
            $fecha_fin = date ( 'j-m-Y' , $fecha_fin );
            dd($fecha_fin);
            $nuevoRegistro = \DB::transaction( function() use ($request, $fecha_fin){
                                $nuevoRegistro = Creditos::create([
                                                    'clientes_id'           => $request->input('idcliente'),
                                                    'planes_id'             => $request->input('idplan'),
                                                    'montos_prestamo_id'    => $request->input('idmonto'),
                                                    'usuarios_creo'         => $request->session()->get('usuario')->id,
                                                    'usuarios_cobrador'     => $request->input('idusuario'),
                                                    'saldo'                 => $request->input('deudatotal') - $request->input('cuota_diaria'),
                                                    'interes'               => 0,
                                                    'deudatotal'            => $request->input('deudatotal'),
                                                    'cuota_diaria'          => $request->input('cuota_diaria'),
                                                    'cuota_minima'          => $request->input('cuota_minima'),
                                                    'fecha_inicio'          => \Carbon\Carbon::parse($request->input('fecha_inicio'))->format('Y-m-d'),
                                                    'fecha_fin'             => \Carbon\Carbon::parse($fecha_fin)->format('Y-m-d'),
                                                    'estado'                => 1,
                                                ]);

                                if( !$nuevoRegistro )
                                    throw new \Exception("Error al crear el registro");
                                else{
                                    $detalleCredito = new CreditosDetalle;
                                    $detalleCredito->creditos_id    = $nuevoRegistro->id;
                                    $detalleCredito->fecha_pago     = \Carbon\Carbon::parse($request->input('fecha_inicio'))->format('Y-m-d');
                                    $detalleCredito->abono          = $request->input('cuota_diaria');
                                    $detalleCredito->estado         = 1;
                                    $detalleCredito->save();

                                    return $nuevoRegistro;
                                }
                            });

            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro creado exitosamente";
            $this->records      = $nuevoRegistro;
                
        } 
        catch (\Exception $e) 
        {
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
            $registro = Creditos::with('cliente','planes','montos','usuariocobrador','detalleCreditos')->find( $id );
            
            if ( $registro ) {
                $cuotas = 0;
                foreach ($registro as $keyRegistro => $valRegistro) {

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

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        
    }

    public function destroy($id)
    {
        //
    }

    public function registrarAbono(Request $request)
    {
        try {
            $creditos = Creditos::where('id', $request->input('idcredito'))->with('planes','montos')->first();
            $cantidadPendiente = CreditosDetalle::where('creditos_id', $creditos->id)->where('estado',0)->first();
            $saldototal = CuotasClientes::where('creditos_id',$request->input('idcredito'))->first();

            if(intval($creditos->saldo) > intval($request->input('abono'))) {
                $faltanteCuota = 0;
                $abonoRestante = 0;

                if ($cantidadPendiente) {

                    $faltanteCuota = $creditos->cuota_diaria - $cantidadPendiente->abono;

                    if ($request->input('abono') < $faltanteCuota) {
                        $cantidadPendiente->abono = $cantidadPendiente->abono + $request->input('abono');
                        $cantidadPendiente->estado = 0;
                        $cantidadPendiente->save();
                    } elseif ($request->input('abono') >= $faltanteCuota) {
                        $cantidadPendiente->abono = $cantidadPendiente->abono + $faltanteCuota;
                        $cantidadPendiente->estado = 1;
                        $cantidadPendiente->save();

                        $abonoRestante = $request->input('abono') - $faltanteCuota;

                        if ($abonoRestante >= $creditos->cuota_diaria) {
                            while ($abonoRestante > 0) {
                                if ($abonoRestante >= $creditos->cuota_diaria) {
                                    $abonoRestante = $abonoRestante - $creditos->cuota_diaria;
                                    $detalleCredito = new CreditosDetalle;
                                    $detalleCredito->creditos_id = $request->input('idcredito');
                                    $detalleCredito->fecha_pago = \Carbon\Carbon::parse(date('Y-m-d'));
                                    $detalleCredito->abono = $creditos->cuota_diaria;
                                    $detalleCredito->estado = 1;
                                    $detalleCredito->save();

                                } else {
                                    $detalleCredito = new CreditosDetalle;
                                    $detalleCredito->creditos_id = $request->input('idcredito');
                                    $detalleCredito->fecha_pago = \Carbon\Carbon::parse(date('Y-m-d'));
                                    $detalleCredito->abono = $abonoRestante;
                                    $detalleCredito->estado = 0;
                                    $detalleCredito->save();
                                    $abonoRestante = $abonoRestante - $creditos->cuota_diaria;
                                }
                            }
                        } elseif ($abonoRestante != 0) {
                            $detalleCredito = new CreditosDetalle;
                            $detalleCredito->creditos_id = $request->input('idcredito');
                            $detalleCredito->fecha_pago = \Carbon\Carbon::parse(date('Y-m-d'));
                            $detalleCredito->abono = $abonoRestante;
                            $detalleCredito->estado = 0;
                            $detalleCredito->save();
                        }
                    }
                } else {
                    $montoAbono = $request->input('abono');

                    if ($montoAbono >= $creditos->cuota_diaria) {
                        while ($montoAbono > 0) {
                            if ($montoAbono >= $creditos->cuota_diaria) {
                                $montoAbono = $montoAbono - $creditos->cuota_diaria;
                                $detalleCredito = new CreditosDetalle;
                                $detalleCredito->creditos_id = $request->input('idcredito');
                                $detalleCredito->fecha_pago = \Carbon\Carbon::parse(date('Y-m-d'));
                                $detalleCredito->abono = $creditos->cuota_diaria;
                                $detalleCredito->estado = 1;
                                $detalleCredito->save();

                            } else {
                                $detalleCredito = new CreditosDetalle;
                                $detalleCredito->creditos_id = $request->input('idcredito');
                                $detalleCredito->fecha_pago = \Carbon\Carbon::parse(date('Y-m-d'));
                                $detalleCredito->abono = $montoAbono;
                                $detalleCredito->estado = 0;
                                $detalleCredito->save();
                                $montoAbono = $montoAbono - $creditos->cuota_diaria;
                            }
                        }
                    } else {
                        $detalleCredito = new CreditosDetalle;
                        $detalleCredito->creditos_id = $request->input('idcredito');
                        $detalleCredito->fecha_pago = \Carbon\Carbon::parse(date('Y-m-d'));
                        $detalleCredito->abono = $request->input('abono');
                        $detalleCredito->estado = 0;
                        $detalleCredito->save();
                    }
                }

                $registroCuotas = CuotasClientes::where('creditos_id', $request->input('idcredito'))->first();

                $saldo = $creditos->deudatotal - $registroCuotas->totalabono;

                if ($saldo == 0) {
                    $creditos->saldo = $saldo;
                    $creditos->estado = 0;
                    $creditos->save();
                } else {
                    $creditos->saldo = $saldo;
                    $creditos->save();
                }

                $this->statusCode = 200;
                $this->result = true;
                $this->message = "Registro creado exitosamente";
                $this->records = $registroCuotas;
            }
            else
                throw new \Exception("La cantidad ingresada es mayor al saldo pendiente");

        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al registrar el abono";
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

    public function cobradorClientes(Request $request){
        try {
            $registros = Creditos::where('usuarios_cobrador',$request->input('idcobrador'))->with('cliente')->get();
            
            if( $registros ){
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registros consultados exitosamente";
                $this->records      = $registros;
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

    public function renovarCredito(Request $request)
    {
        try {
            $registros = Creditos::where('usuarios_cobrador',$request->input('idcobrador'))->with('cliente')->get();
            
            if( $registros ){
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registros consultados exitosamente";
                $this->records      = $registros;
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

    public function boletaPDF(Request $request){

        $registro = Creditos::with('cliente','planes','montos','usuariocobrador','detalleCreditos')->find( $request->input('credito_id') );
        
        if ( $registro ) {

            $cuotas = 0;
            foreach ($registro as $keyRegistro => $valRegistro) {

                if( $valRegistro['estado'] == 1 )
                {
                    $cuotas = $cuotas + 1;
                }
            }
            $registro->cuotasPagadas = $cuotas;
            $registro->cuotasPendientes = $registro->planes->dias - $registro->cuotas;

            $pdf = \App::make('dompdf');
            $pdf = \PDF::loadView('pdf.boleta', ['data' => $registro])->setPaper('letter')->setOrientation('landscape');

            return $pdf->download('boleta.pdf');
        }
    }
}

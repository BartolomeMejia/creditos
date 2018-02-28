<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Usuarios;
use Auth;
use DB;
use Session;

//revisar el usuario que no sea repedito
//validar el password que vaya vacio

class UsuariosController extends Controller
{
    public $statusCode  = 200;
    public $result      = false;
    public $message     = "";
    public $records     = [];

    public function index()
    {
        try {
            $registros = Usuarios::where("estado", 1)->with('tipoUsuarios','sucursal')->get();

            if( $registros ){
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registros consultados exitosamente";
                $this->records      = $registros;
            }
            else
                throw new \Exception("No se encontraron registros");
                
        } catch (\Exception $e) {
            $this->statusCode   = 404;
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

    
    public function create()
    {
        //
    }

    
    public function store(Request $request)
    {
        try {

            $registro = Usuarios::where('user',strtolower($request->input('user')))->get();

            if(count($registro) == 0)
            {
                $nuevoRegistro = \DB::transaction( function() use ( $request){
                                    $nuevoRegistro = Usuarios::create([
                                                        'tipo_usuarios_id'  => $request->input('idtipousuario'),
                                                        'nombre'            => $request->input('nombre'),
                                                        'user'              => strtolower($request->input('user')),
                                                        'estado'            => 1,
                                                        'sucursales_id'     => $request->input('idsucursal'),
                                                        'password'          => \Hash::make($request->input('password')),
                                                        'password_2'        => \Hash::make($request->input('password2')),

                                                    ]);

                                    if( !$nuevoRegistro )
                                        throw new \Exception("No se pudo crear el registro");
                                    else
                                        return $nuevoRegistro;
                                });
            }
            else
                throw new \Exception("Usuario ingresado ya existe, favor verifica");
                  
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
            $registro = Usuarios::with('tipoUsuarios','sucursal')->find( $id );

            if( $registro ){
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultado exitosamente";
                $this->records      = $registro;
            }
            else
                throw new \Exception("No se encontró el registro");
                
        } catch (\Exception $e) {
            $this->statusCode   = 404;
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

            $registroUsuario = Usuarios::where('user',strtolower($request->input('user')))->get();

            if( count($registroUsuario) == 0 )
                \DB::beginTransaction();
                $registro = Usuarios::find( $id );
                $registro->tipo_usuarios_id = $request->input('idtipousuario', $registro->tipo_usuarios_id);
                $registro->nombre           = $request->input('nombre', $registro->nombre);
                $registro->user             = strtolower($request->input('user', $registro->user));
                $registro->estado           = $request->input('estado', $registro->estado);
                $registro->sucursales_id    = $request->input('idsucursal', $registro->sucursales_id);

                if($request->input("password")!="")
                    $registro->password       = \Hash::make($request->input('password'));
                if($request->input("password2")!="")   
                    $registro->password_2     = \Hash::make($request->input('password2')); 

                $registro->password_2       = $request->input('password2', $registro->password_2);
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
                                $registro = Usuarios::find( $id );
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

    public function login(Request $request)
    {
        try
        {
            if (Auth::attempt(['user'=> $request->input('user'),'password'=> $request->input('password')]))
            {
                //Session::put('idUsuario', Auth::user()->id);

                $request->session()->put('usuario', Auth::user());

                $this->records      =   [Auth::user()];
                $this->message      =   "Sesión iniciada";
                $this->result       =   true;
                $this->statusCode   =   200;
            }
            else
            {
                throw new \Exception("Usuario o password incorrecto");
                
            }
        }
        catch (\Exception $e)
        {
            $this->statusCode   =   200;
            $this->message      =   env('APP_DEBUG')?$e->getMessage():'Ocurrió un problema al iniciar la sesión';
            $this->result       =   false;
        }
        finally
        {
            $response = 
            [
                'message'   =>  $this->message,
                'result'    =>  $this->result,
                'records'   =>  $this->records
            ];
            
            return response()->json($response, $this->statusCode);
        }
    }

    public function listaCobradores(Request $request){
        try {

            $registros = Usuarios::where('tipo_usuarios_id',4)->where('sucursales_id',$request->session()->get('usuario')->sucursales_id)->get();

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
}

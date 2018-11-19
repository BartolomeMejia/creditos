<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use App\TipoUsuarios;
use App\Creditos;
use App\Usuarios;
use App\Http\Traits\customerTrait;

class DashboardController extends Controller
{
    public $statusCode = 200;
    public $result = false;
    public $message = '';
    public $records;

    use customerTrait;

    public function dashboard(Request $request)
    {
        try {

            $branchId = 0;
            $customers = 0;
            $customersWithCreditToDay = [];
            $resumenDashboard = new \stdClass();
            
            $branchId = $request->session()->get('usuario')->tipo_usuarios_id == 1 ? 0 : $request->session()->get('usuario')->sucursales_id;

            if($branchId == 0){
                $customers = Creditos::where("estado", 1)->count();
                $customersWithCreditToDay = $this->getCustomersWithCreditToDay();
                $collectors = Usuarios::where("tipo_usuarios_id", 4)->where("estado", 1)->count();
            } else {
                $customers = Creditos::where("estado", 1)->where("sucursal_id", $branchId)->count();
                $customersWithCreditToDay = $this->getCustomersWithCreditToDay()->filter(function ($item) use ($branchId){ return $item->sucursal_id == $branchId;});
                $collectors = Usuarios::where("tipo_usuarios_id", 4)->where("estado", 1)->where("sucursales_id", $branchId)->count();
            }
            
            $resumenDashboard->customers = $customers;
            $resumenDashboard->customersWithCreditToDay = $customersWithCreditToDay->count();
            $resumenDashboard->customersWithCreditNoToDay = intval($customers) - intval($customersWithCreditToDay->count());
            $resumenDashboard->collectors = $collectors;
            

            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro consultado exitosamente";
            $this->records      = $resumenDashboard;
        } 
        catch (\Exception $e) 
        {
            $this->statusCode = 200;
            $this->result = false;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar los datos";
            
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
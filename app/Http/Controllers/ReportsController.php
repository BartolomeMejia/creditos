<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use App\Http\Traits\reportsTrait;

class ReportsController extends Controller
{
    public $statusCode = 200;
    public $result = false;
    public $message = '';
    public $records;

    use reportsTrait;

    public function general(Request $request){
        try {
            $branch = $request->input('branch');
            $general = new \stdClass();          
            $general->customers = $this->getCountCustomers("", "", "", "", $branch);
            $general->revenueTotals =  $this->getRevenueTotals("", "", "", "", $branch);
            $general->totalPendingReceivable =  $this->getPendingReceivable("", "", "", "", $branch);
            $general->totalReceivable =  $this->getTotalReceivable("", "", "", "", $branch);
            $general->totalGeneratedInterests = $this->getGeneratedInterests("", "", "", "", $branch);

            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro consultado exitosamente";
            $this->records      = $general;
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

    public function collector(Request $request){
        try {
            $collector = $request->input('collector');
            $dateInit = $request->input('date-init');
            $dateFinal = $request->input('date-final');
            $plan = $request->input('plan');
            $branch = $request->input('branch');

            $general = new \stdClass();          
            $general->customers = $this->getCountCustomers($collector, $dateInit, $dateFinal, $plan, $branch);
            $general->revenueTotals =  $this->getRevenueTotals($collector, $dateInit, $dateFinal, $plan, $branch);
            $general->totalPendingReceivable =  $this->getPendingReceivable($collector, $dateInit, $dateFinal, $plan, $branch);
            $general->totalReceivable =  $this->getTotalReceivable($collector, $dateInit, $dateFinal, $plan, $branch);
            $general->totalGeneratedInterests = $this->getGeneratedInterests($collector, $dateInit, $dateFinal, $plan, $branch);

            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro consultado exitosamente";
            $this->records      = $general;
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
}
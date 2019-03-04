<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;

use App\Creditos;
use App\DetallePagos;
use App\Http\Traits\detailsPaymentsTrait;

trait reportsTrait {

    use detailsPaymentsTrait;

    public function getCountCustomers(Request $request){
    
        $countCustomers = new \stdClass();        
        $credits = $this->getCreditWithPlansAmount($request);                
                             
        $countCustomers->withCredit = $credits->groupBy('clientes_id')->count();
        $countCustomers->withCreditToDay = $this->getCustomersWithCreditToDay($credits)->groupBy('clientes_id')->count();
        $countCustomers->withCreditNoToDay = intval($countCustomers->withCredit) - intval($countCustomers->withCreditToDay);
        
        return $countCustomers;
    }

    public function getRevenueTotals(Request $request) {

        $totalCharged = new \stdClass();        
        $credits = $this->getCreditWithPlansAmount($request);

        if ($credits->count() > 0) {            
            $totalCharged = 0;
            foreach ($credits as $credit){
                $totalCharged += DetallePagos::where('credito_id', $credit->id)->where('estado', 1)->get()->sum('abono');
            }
        } else {        
            $totalCharged = 0;
        }
        
        return $totalCharged;
    }

    public function getPendingReceivable(Request $request) {        
        $totalPendingReceivable = 0;
        $totalPendingReceivable = $this->getCreditWithPlansAmount($request)->sum('saldo');
        return $totalPendingReceivable;
    }

    public function getTotalReceivable(Request $request) {
        $totalReceivable = 0;
        $totalReceivable = $this->getCreditWithPlansAmount($request)->sum('deudatotal');
        return $totalReceivable;
    }

    public function getGeneratedInterests(Request $request){
        $generatedInterests = 0;
        $credits = $this->getCreditWithPlansAmount($request);
        if ($credits->count() > 0) {
            foreach ($credits as $credit) {
                $generatedInterests += ($credit->montos->monto * $credit->planes->porcentaje) / 100;
            }
        } 
        return $generatedInterests;
    }

    public function getCustomersWithCreditToDay($credits) {        
        $countCredits = $credits->map(function($item,$key){            
            $today = \Carbon\Carbon::now();
            $startDate = \Carbon\Carbon::parse($item->fecha_inicio);
            $days = $today->diffInDays($startDate);
            $minimumPayment = ($days - 3) * $item->cuota_diaria;            
            $totalPayment = $this->getDetailsPayments($item->id)->totalPayment; 

            if($totalPayment)
                if($totalPayment > $minimumPayment)
                    return $item;
        });
        
        return $countCredits->filter(function ($item){ return $item != null;});
    }

    private function getCreditWithPlansAmount(Request $request){        
        
        $collector = $request->input('collector');
        if ($request->input('date-init') != null && $request->input('date-final') != null) {
            $dateInit = \Carbon\Carbon::parse($request->input('date-init'))->format('Y-m-d');
            $dateFin = \Carbon\Carbon::parse($request->input('date-final'))->format('Y-m-d');
        } else {
            $dateInit = "";
            $dateFin = "";
        }
        $plan = $request->input('plan');
        $branch = $request->input('branch');
        $credits = "";        
        if ( $collector != "" ) {
            $credits = Creditos::where('sucursal_id', $branch)
                            ->where('usuarios_cobrador', $collector)
                            ->whereBetween('fecha_inicio', [$dateInit, $dateFin])
                            ->where('estado', 1)
                            ->with('planes', 'montos')
                            ->get();
        } else if ($dateInit !=  "" && $dateFin != "") {
            $credits = Creditos::where('sucursal_id', $branch)
                            ->whereBetween('fecha_inicio', [$dateInit, $dateFin])
                            ->where('estado', 1)
                            ->with('planes', 'montos')
                            ->get();
        } else {            
            $credits = Creditos::where('sucursal_id', $branch)                            
                            ->where('estado', 1)
                            ->with('planes', 'montos')
                            ->get();
        }
        
        if ($plan != "" && $plan != 0) {
            return $credits->filter(function ($item) use ($plan){ 
                    return $item->planes_id == $plan;        
            });
        } else {
            return $credits;
        }
    }
}
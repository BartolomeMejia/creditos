<?php

namespace App\Http\Traits;

use App\Creditos;
use App\DetallePagos;
use App\Http\Traits\detailsPaymentsTrait;

trait reportsTrait {

    use detailsPaymentsTrait;

    public function getCountCustomers($collector, $dateInit, $dateFin, $plan, $branch){
        $countCustomers = new \stdClass();        
        $credits = $this->getCreditGroupCustomer($collector, $dateInit, $dateFin, $branch)
                        ->filter(function ($item) use ($plan){ 
                            if($plan != "")
                                return $item->planes_id == $plan;
                            else
                                return $item;});                         
                             
        $countCustomers->withCredit = $credits->groupBy('clientes_id')->count();
        $countCustomers->withCreditToDay = $this->getCustomersWithCreditToDay($credits)->groupBy('clientes_id')->count();
        $countCustomers->withCreditNoToDay = intval($countCustomers->withCredit) - intval($countCustomers->withCreditToDay);
        
        return $countCustomers;
    }

    public function getRevenueTotals($collector, $dateInit, $dateFin, $plan, $branch) {
        $totalCharged = new \stdClass();
        
        $credits = $this->getCreditWithPlansAmount($collector, $dateInit, $dateFin, $branch)
                        ->filter(function ($item) use ($plan){ 
                            if($plan != "")
                                return $item->planes_id == $plan;
                            else
                                return $item;
                        });

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
    public function getPendingReceivable($collector, $dateInit, $dateFin, $plan, $branch) {
        $totalPendingReceivable = 0;
        $totalPendingReceivable = $this->getCreditWithPlansAmount($collector, $dateInit, $dateFin, $branch)
                                        ->filter(function ($item) use ($plan){ 
                                            if($plan != "")
                                                return $item->planes_id == $plan;
                                            else
                                                return $item;
                                        })
                                        ->sum('saldo');
        return $totalPendingReceivable;
    }

    public function getTotalReceivable($collector, $dateInit, $dateFin, $plan, $branch) {
        $totalReceivable = 0;
        $totalReceivable = $this->getCreditWithPlansAmount($collector, $dateInit, $dateFin, $branch)
                                ->filter(function ($item) use ($plan){ 
                                    if($plan != "")
                                        return $item->planes_id == $plan;
                                    else
                                        return $item;
                                })
                                ->sum('deudatotal');
        return $totalReceivable;
    }

    public function getGeneratedInterests($collector, $dateInit, $dateFin, $plan, $branch){
        $generatedInterests = 0;
        $credits = $this->getCreditWithPlansAmount($collector, $dateInit, $dateFin, $branch)
                        ->filter(function ($item) use ($plan){ 
                            if($plan != "")
                                return $item->planes_id == $plan;
                            else
                                return $item;
                        });
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

    private function getCreditGroupCustomer($collector, $dateInit, $dateFin, $branch) {               
        if ( $collector != "" ) {
            return Creditos::where('sucursal_id', $branch)
                            ->where('usuarios_cobrador', $collector)
                            ->where('fecha_inicio','<=',date($dateFin))
                            ->where('estado', 1)
                            ->with('planes')                            
                            ->get();
        } else if ($dateInit !=  "" && $dateFin != ""){
            return Creditos::where('sucursal_id', $branch)
                            ->whereDay('fecha_inicio','<=',$dateFin)
                            ->where('estado', 1)
                            ->with('planes')                            
                            ->get();
        } else {
            return Creditos::where('sucursal_id', $branch)
                            ->whereDay('fecha_inicio','<=',$dateFin)
                            ->where('estado', 1)
                            ->with('planes')                            
                            ->get();
        }
    }

    private function getCreditWithPlansAmount($collector, $dateInit, $dateFin, $branch){        
        if ( $collector != "" ) {
            return Creditos::where('sucursal_id', $branch)
                            ->where('usuarios_cobrador', $collector)
                            ->whereBetween('fecha_inicio', [$dateInit, $dateFin])
                            ->where('estado', 1)
                            ->with('planes', 'montos')
                            ->get();
        } else if ($dateInit !=  "" && $dateFin != ""){
            return Creditos::where('sucursal_id', $branch)
                            ->whereBetween('fecha_inicio', [$dateInit, $dateFin])
                            ->where('estado', 1)
                            ->with('planes', 'montos')
                            ->get();
        } else {
            return Creditos::where('sucursal_id', $branch)
                            ->whereBetween('fecha_inicio', [$dateInit, $dateFin])
                            ->where('estado', 1)
                            ->with('planes', 'montos')
                            ->get();
        }
    }
}
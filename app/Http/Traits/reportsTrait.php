<?php

namespace App\Http\Traits;

use App\Creditos;
use App\DetallePagos;
use App\Http\Traits\customerTrait;

trait reportsTrait {

    use customerTrait;

    public function getCountCustomers($collector, $dateInit, $dateFin, $plan, $branch){
        $countCustomers = new \stdClass();
        $countCustomers->withCredit = $this->getCreditGroupCustomer($collector, $dateInit, $dateFin, $branch)
                                            ->filter(function ($item) use ($plan){ 
                                                if($plan != "")
                                                    return $item->planes_id == $plan;
                                                else
                                                    return $item;
                                            })
                                            ->count();
        $countCustomers->withCreditToDay = $this->getCustomersWithCreditToDay()->count();
        $countCustomers->withCreditNoToDay = intval($countCustomers->withCredit) - intval($countCustomers->withCreditToDay);
        
        return $countCustomers;
    }

    public function getRevenueTotals($collector, $dateInit, $dateFin, $plan, $branch) {
        $totalCharged = new \stdClass();
        $credits = $this->getCreditWithPlansAmount($collector, $dateInit, $dateFin, $branch);
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
        $totalPendingReceivable = $this->getCreditWithPlansAmount($collector, $dateInit, $dateFin, $branch)->sum('saldo');
        return $totalPendingReceivable;
    }

    public function getTotalReceivable($collector, $dateInit, $dateFin, $plan, $branch) {
        $totalReceivable = 0;
        $totalReceivable = $this->getCreditWithPlansAmount($collector, $dateInit, $dateFin, $branch)->sum('deudatotal');
        return $totalReceivable;
    }

    public function getGeneratedInterests($collector, $dateInit, $dateFin, $plan, $branch){
        $generatedInterests = 0;
        $credits = $this->getCreditWithPlansAmount($collector, $dateInit, $dateFin, $branch);
        if ($credits->count() > 0) {
            foreach ($credits as $credit) {
                $generatedInterests += ($credit->montos->monto * $credit->planes->porcentaje) / 100;
            }
        } 
        return $generatedInterests;
    }

    private function getCreditGroupCustomer($collector, $dateInit, $dateFin, $branch) {        
        if ( $collector != "" ) {
            return Creditos::where('sucursal_id', $branch)
                            ->where('usuarios_cobrador', $collector)
                            ->whereDay('fecha_inicio','<=',$dateFin)
                            ->where('estado', 1)
                            ->with('planes')
                            ->groupBy('clientes_id')
                            ->get();
        } else if ($dateInit !=  "" && $dateFin != ""){
            return Creditos::where('sucursal_id', $branch)->where('estado', 1)->with('planes')->groupBy('clientes_id')->get()->count();
        } else {
            return Creditos::where('sucursal_id', $branch)->where('estado', 1)->with('planes')->groupBy('clientes_id')->get()->count();
        }
    }

    private function getCreditWithPlansAmount($collector, $dateInit, $dateFin, $branch){        
        if ( $collector != "" ) {
            return Creditos::where('sucursal_id', $branch)->where('estado', 1)->with('planes', 'montos')->get();
        } else if ($dateInit !=  "" && $dateFin != ""){
            return Creditos::where('sucursal_id', $branch)->where('estado', 1)->with('planes', 'montos')->get();
        } else {
            return Creditos::where('sucursal_id', $branch)->where('estado', 1)->with('planes', 'montos')->get();
        }
    }
}
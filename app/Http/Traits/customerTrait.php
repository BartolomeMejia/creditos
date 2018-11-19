<?php

namespace App\Http\Traits;

use App\Creditos;
use App\CuotasClientes;
use App\Http\Traits\detailsPaymentsTrait;

trait customerTrait {

    use detailsPaymentsTrait;
    
    public function getCustomersWithCreditToDay() {
        
        $credits = Creditos::where("estado",1)->with('planes')->get();
        
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

    public function getDayOverdueCustomer($creditId, $customerId){
        $totalDays = 0;
        $credit = Creditos::where("estado",1)
                    ->where('id', $creditId)
                    ->with('planes')
                    ->first();
        if($credit){
            $today = \Carbon\Carbon::now();
            $startDate = \Carbon\Carbon::parse($credit->fecha_inicio);
            $days = $today->diffInDays($startDate);
            $totalFees = $this->getDetailsPayments($item->id)->totalFees; 
            if($totalFees)
                $totalDays = $days - $totalFees;
            else
                $totalDays = $days;
        }
        return $totalDays;
    }
}

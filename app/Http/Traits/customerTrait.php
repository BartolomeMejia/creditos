<?php

namespace App\Http\Traits;

use App\Creditos;
use App\CuotasClientes;

trait customerTrait {
    public function getCustomersWithCreditToDay() {
        
        $credits = Creditos::where("estado",1)->with('planes')->get();
        
        $countCredits = $credits->map(function($item,$key){
            
            $today = \Carbon\Carbon::now();
            $startDate = \Carbon\Carbon::parse($item->fecha_inicio);
            $days = $today->diffInDays($startDate);
            $minimumPayment = ($days - 3) * $item->cuota_diaria;
            $totalPayment = CuotasClientes::where('creditos_id',$item->id)->first();

            if($totalPayment)
                if($totalPayment->totalabono > $minimumPayment)
                    return $item;
        });
        
        return $countCredits->filter(function ($item){ return $item != null;});
    }
}

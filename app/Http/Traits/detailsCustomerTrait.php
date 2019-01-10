<?php

namespace App\Http\Traits;

use App\Creditos;
use App\CuotasClientes;

trait detailsCustomerTrait {

    public function getDayOverdueCustomer($creditId, $totalFees){
        $totalDays = 0;
        $credit = Creditos::where("estado",1)
                    ->where('id', $creditId)
                    ->with('planes')
                    ->first();
        if($credit){
            $today = \Carbon\Carbon::now();
            $startDate = \Carbon\Carbon::parse($credit->fecha_inicio);
            $days = $today->diffInDays($startDate);
            if($totalFees)
                $totalDays = $days - $totalFees;
            else
                $totalDays = $days;
        }
        return $totalDays;
    }
}
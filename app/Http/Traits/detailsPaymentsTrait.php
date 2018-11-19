<?php

namespace App\Http\Traits;

use App\Creditos;
use App\DetallePagos;

trait detailsPaymentsTrait {
    public function getDetailsPayments($creditId) {
        
        $detailPayment = new \stdClass();
        $credit = Creditos::where("id", $creditId)->where("estado", 1)->with('planes', 'montos')->first();        
        if($credit){
            $detailsPayments = DetallePagos::where('credito_id', $credit->id)->where('estado', 1)->get();
            
            if($detailsPayments->count() > 0){     
                $detailPayment->totalPayment = $detailsPayments->sum('abono');
                $detailPayment->totalFees =  intval($detailPayment->totalPayment/$credit->cuota_diaria);   
                $paymentPaid = $detailPayment->totalPayment % $credit->cuota_diaria;
                if($paymentPaid != 0){ 
                    $detailPayment->paymentPaid = $paymentPaid;
                } else {
                    $detailPayment->paymentPaid = 0;
                }
                $detailPayment->paymentPercentage = round(($detailPayment->totalFees * 100)/($credit->planes->dias - $detailPayment->totalFees), 2);
            } else {
                $detailPayment->totalPayment = 0;
                $detailPayment->totalFees = 0;
                $detailPayment->paymentPaid = 0;
                $detailPayment->paymentPercentage = 0;
            }
        }
        
        return $detailPayment;
    }
}

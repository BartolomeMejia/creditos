<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CreditosDetalle extends Model
{
    protected $table = 'credito_detalle';
	protected $fillable = ['creditos_id','fecha_pago','abono','estado'];
}

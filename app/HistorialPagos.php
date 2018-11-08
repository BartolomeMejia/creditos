<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HistorialPagos extends Model
{
    protected $table = 'historial_pagos';
	protected $fillable = ['credito_id','detalle_id','monto','fecha_pago','estado', 'tipo'];
}

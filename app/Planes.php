<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Planes extends Model
{
    protected $table = 'planes';
	protected $fillable = ['descripcion','dias','porcentaje','sucursales_id'];

	public function sucursal(){
		return $this->hasOne('App\sucursales','id','sucursales_id');
	}
}

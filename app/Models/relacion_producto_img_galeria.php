<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class relacion_producto_img_galeria extends Model
{
    public $timestamps = false;
    protected $table = "ralacion_producto_img_galeria";
    protected $primaryKey = 'id_ralacion_producto_img_galeria';
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class reg_codenv extends Model
{
    public $timestamps = false;
    protected $table = "reg_cod_env";

    protected $fillable = ['cod_env', 'correo'];

    protected $primaryKey = 'id_reg_cod_env';
}

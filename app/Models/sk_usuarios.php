<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class sk_usuarios extends Authenticatable
{
    use Notifiable, HasApiTokens;

    protected $table = "sk_usuarios";

    protected $primaryKey = 'id';

    protected $fillable = [
        'cod_usuario',
        'estatus',
        'tipo',
        'email',
        'nom_usuario', 
        'password', 
        'remember_token', 
        'pass_usuario', 
        'pass_usuario_p', 
        'categoria', 
        'descrip_usuario', 
        'des_usuario', 
        'ape_usuario', 
        'sex_usuario', 
        'edad_usuario', 
        'pais_usuario', 
        'fecha_usuario', 
        'cod_definicion', 
        'cod_concepto', 
        'val_definicion', 
        'bank_data', 
        'dni_usuario', 
        'dir_usuario', 
        'body_data', 
        'altura_usuario', 
        'color_usuario', 
        'ojos_usuario', 
        'pelo_usuario', 
        'fumar_usuario', 
        'comida_usuario', 
        'nino_usuario', 
        'nino_usuario', 
        'cod_img',
        'bank_name',
        'bank_swift',
        'bank_paypal',
        'hash',
        'active',
        'email_verified_at',
        'created',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'pass_usuario',
        'pass_usuario_p',
        'password'
    ];

    public function images(){
        return $this->hasMany('App\Models\reg_perfil_usuario_img', 'cod_usuario', 'cod_usuario');
    }
}



<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class controladorHelpers extends Controller
{
    /////TAREAS/////
    public  $CREAR = 'crear';
    public  $ACTUALIZAR = 'actualizar';
    public  $CARRITO = 'carrito';
    public  $OFERTA = 'oferta';
    public  $DEVOLUCION = 'devolucion';
    public  $FACTURACION = 'facturacion';
    public  $SALIDA = 'salida';
    public  $ENTREGA = 'entrega';
    public  $ENVIADO = 'enviado';
    public  $ENTREGADO = 'entregado';
    public  $FINALIZADO = 'finalizado';

    //////ESTATUS///////
    public  $PENDIENTE = 'P';
    public  $DELIVERY = 'D';
    public  $RECIBIDO = 'R';
    public  $COMPLETO = 'C';
    public  $DESEO = 'GG';

    /////PROCESOS LIMITES//////

    public $MSM = 'msm';
    public $SWIPE = 'swipe';

    /////PROCESOS LIMITES VALORES//////

    public $CERO = '0';
    public $UNIDAD = '1';

    /////PROCESOS LIMITES TIEMPO//////

    public $LIMITE = '18';

}

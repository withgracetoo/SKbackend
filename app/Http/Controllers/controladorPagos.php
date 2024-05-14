<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Charge;

class controladorPagos extends Controller
{
    public function getPagosPedido(Request $id){
        try {
            $data = $id->all();
            
            Stripe::setApiKey(config('services.stripe.secret'));
            $charge = Charge::create([
                'amount' => floatval($data['amount']) * 100,
                'currency' => $data['currency'],
                'description' => 'Pagos',
                'source' => $data['token'],
        ]);
        return response()-> json(['pago'=>$charge],200);
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
}

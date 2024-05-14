<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class controladorDefiniciones extends Controller
{
    public function getDefinicionSitema(Request $id){
        try {
            $data = $id->all();

                $objeto = DB::select('call getDefinicionSitema(
                                                        "'.$data['des_definicion'].'",
                                                        "'.$data['concepto_definicion'].'")');
                
                    if (count($objeto) > 0) {
                        return response()-> json(['def'=>$objeto],200);
                    }else{
                        $message = 'This user cannot be registered';
                    return response()-> json(['message'=>$message],404);
                    }
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getCodStorage(Request $id){
        try {
            $data = $id->all();
            $ip = $_SERVER['REMOTE_ADDR'];
                $objeto = DB::select('call getCodStorage(
                                                        "'.$ip.'")');
                
                    if (count($objeto) > 0) {
                        return response()-> json(['resul'=>$objeto],200);
                    }else{
                        $message = 'This code not found';
                    return response()-> json(['message'=>$message],404);
                    }
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
}

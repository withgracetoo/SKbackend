<?php

namespace App\Http\Controllers;

use App\Http\Controllers\controladorHelpers;
use DB;

class controladorMultimedia extends Controller
{
    public function getMultimedia(Request $id){
        try {
            $helper = new controladorHelpers();
        
            $data = $id->all();
            $objeto = DB::select('call getMultimedia(
                                                "'.$data['cod_usuario'].'",
                                                "'.$data['cod_multimedia'].'"
                                                )');

                if (count($objeto) > 0) {
                    $message = 'Video cargado de forma correcta';
                return response()-> json(['message'=>$message],200);
                
            }else{
                $message = 'Error de consulta';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getMultimediaGaleria(Request $id){
        try {
            $helper = new controladorHelpers();
        
            $data = $id->all();
            $objeto = DB::select('call getMultimediaGaleria("'.$data['cod_usuario'].'")');

            return response()-> json(['multimedia'=>$objeto],200);
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getQuitarMultimedia(Request $id){
        try {
            $helper = new controladorHelpers();
            $data = $id->all();
            $objeto = DB::select('call getQuitarMultimedia(
                                                "'.$data['cod_usuario'].'",
                                                "'.$data['cod_multimedia'].'"
                                                )');
                if (count($objeto) > 0) {
                return response()-> json(['message'=>$objeto],200);
            }else{
                $message = 'No hay videos disponibles';
                return response()-> json(['message'=>$message],404);
            }
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
}

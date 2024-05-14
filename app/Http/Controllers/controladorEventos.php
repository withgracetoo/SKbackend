<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\sec_usuario_rol;
use App\Models\sk_usuarios;
use App\Models\sesiones_app_usuarios;
use App\Models\sk_visitantes;
use App\Models\sesiones_app_visitantes;
use App\Models\inv_depositos_control;
use App\Models\reg_perfil_usuario_img;
use App\Models\sec_like_dislike_mmbr_dlr;
use DB;

class controladorEventos extends Controller
{
    public function getEventosLikeDislike(Request $id){
        try {
            $ip = $_SERVER['REMOTE_ADDR'];
            $data = $id->all();
            $objeto_evento = sec_like_dislike_mmbr_dlr::where('cod_mmbr',$data['cod_mmbr'])
                                                        ->where('cod_dlr',$data['cod_dlr'])
                                                        ->where('estatus','A')->get();
            if (count($objeto_evento) <= 0) {
                $val_event = [  
                    'cod_mmbr' => $data['cod_mmbr'],
                    'cod_dlr' => $data['cod_dlr'],
                    'tipo' => $data['tipo'],
                    'estatus' => 'A',
                    'fecha_inicio' => '',
                    'hora_inicio' => ''];
             DB::table('sec_like_dislike_mmbr_dlr')->insert([ $val_event ]);
        
                $message = 'Thanks for choosing us';
                return response()-> json(['message'=>$message],200);
            }else{
                $message = 'Thanks for choosing us';
                return response()-> json(['message'=>$message],200);
            }
    
        } catch (\Throwable $th) {
            return response()-> json(['message'=>$th],404);
        }
    }
    public function getFavoritos(Request $id){
        try {
            $data = $id->all();
            $cod_usuario;
            $objeto_mmbr = sec_like_dislike_mmbr_dlr::where('cod_mmbr',$data['cod_usuario'])->where('estatus','A')->get();
            $message = 'Data loaded successfull';
            if (count($objeto_mmbr) > 0) {
                foreach ($objeto_mmbr as $i => $value) {
                    $objeto = sk_usuarios::where('cod_usuario',$value['cod_dlr'])->where('estatus','A')->get();
                    $objeto_img = reg_perfil_usuario_img::where('estatus','A')->where('cod_usuario',$value['cod_dlr'])->get();
                    $value['des_usuario'] = $objeto[0]['nom_usuario'];
                    $value['cod_usuario'] = $objeto[0]['cod_usuario'];
                    $value['tipo_usuario'] = $objeto[0]['tipo'];
                    $value['tipo_evento'] = $value['tipo'];
                    $value['cod_img'] = $objeto_img[0]['cod_img'];
                }
                return response()-> json(['usuario'=>$objeto_mmbr,'message'=>$message],200);
            }else{
                $objeto_dlr = sec_like_dislike_mmbr_dlr::where('cod_dlr',$data['cod_usuario'])->where('estatus','A')->get();
                foreach ($objeto_dlr as $i => $value) {
                    $objeto = sk_usuarios::where('cod_usuario',$value['cod_mmbr'])->where('estatus','A')->get();
                    $objeto_img = reg_perfil_usuario_img::where('estatus','A')->where('cod_usuario',$value['cod_mmbr'])->get();
                    $value['des_usuario'] = $objeto[0]['nom_usuario'];
                    $value['cod_usuario'] = $objeto[0]['cod_usuario'];
                    $value['tipo_usuario'] = $objeto[0]['tipo'];
                    $value['tipo_evento'] = $value['tipo'];
                    $value['cod_img'] = $objeto_img[0]['cod_img'];
                }
                return response()-> json(['usuario'=>$objeto_dlr ,'message'=>$message],200);
            }
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getQuitarDislike(Request $id){
        try {
            $data = $id->all();
            $objeto = DB::select('call getQuitarDislike(
                                                    "'.$data['cod_mmbr'].'",
                                                    "'.$data['cod_dlr'].'",
                                                    "'.$data['tipo'].'"
                                                    )');

            return response()-> json(['resul'=>$data],200);
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
}

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
use App\Models\reg_codenv;
use App\Mail\controladorCorreo;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\controladorENV;
use Symfony\Component\Console\Output\ConsoleOutput;
use DB;
use Illuminate\Support\Str;

class controladorAccesos extends Controller
{
    public function getMenuAcceso(Request $id){
        try {
            $data = $id->all();
            $objeto = sec_usuario_rol::where('cod_usuario',$data['cod_usuario'])->where('des_menu',$data['des_menu'])->get();
            $message = 'error al consultar';
            if ($objeto !== null) {
                return response()-> json($objeto,200);
            }else{
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (\Throwable $th) {
            return response()-> json(['message'=>$th],404);
        }
    }
    public function getPerfilAcceso(Request $id){
        try {
            $data = $id->all();
            $objeto_user = sk_usuarios::where('nom_usuario',$data['nom_usuario'])->where('pass_usuario',$data['pass_usuario'])->get();
            
            if (count($objeto_user) > 0) {
                $objeto_sec= sesiones_app_usuarios::where('cod_usuario',$objeto_user[0] ['cod_usuario'])->where('estatus','A')->get();
                $arry = ['usuario'=>$objeto_user,'sesiones'=>$objeto_sec];
                return response()-> json($arry,200);
            }else{
                $message = 'Credenciales incorrectas';
                return response()-> json(['404'=>$message],404);
            }
            
        } catch (\Throwable $th) {
            return response()-> json(['try_catch'=>$objeto_user],404);
        }
    }
    public function getPerfilAccesoVisitante(Request $id){
        try {
            $ip = $_SERVER['REMOTE_ADDR'];
            $data = $id->all();

                $objeto_usuario_visit = DB::select('call getPerfilAccesoVisitante(
                                                        "'.$ip .'")');
                
                    if (count($objeto_usuario_visit) > 0) {
                        $objeto_sesion_visit = DB::select('call getSesionActivaVisitante(
                                                                                "'.$objeto_usuario_visit[0]->resul .'",
                                                                                "'.$ip .'")');
                        return response()-> json(['sesion'=>$objeto_sesion_visit],200);
                    }else{
                        $message = 'This user cannot be registered';
                    return response()-> json(['message'=>$message],404);
                    }
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getPerfilAccesoMiembros(Request $id){
        try {
            $ip = $_SERVER['REMOTE_ADDR'];
            $data = $id->all();
            $objeto_user_validar = sk_usuarios::where('email',$data['email'])->get();

            if (count($objeto_user_validar) > 0) {

                if ($objeto_user_validar[0]['active'] == 0)
                {
                    $message = 'The email account has not been verified';
                     return response()-> json(['status'=>'Unverified','message'=>$message],200);
                }

                $objeto_user_sesion_activa = sesiones_app_usuarios::where('cod_usuario',$objeto_user_validar[0]['cod_usuario'])->where('estatus','A')->get();
                if (count($objeto_user_sesion_activa) > 0) {
                    DB::table('sesiones_app_usuarios')->where('estatus',$activo = 'A')->where('cod_usuario',$objeto_user_validar[0]['cod_usuario'])->update(array('estatus' => $activo = 'I'));
                    $val_sesion = [  
                            'cod_sesion' => 'SS',
                            'cod_usuario' => $objeto_user_validar[0]['cod_usuario'],
                            'estatus' => $activo = 'A',
                            'fecha_inicio' => $ip,
                            'hora_inicio' => $ip,
                            'dir_ip_client' => $ip];
                     DB::table('sesiones_app_usuarios')->insert([ $val_sesion ]);
                     $objeto_user = sesiones_app_usuarios::where('cod_sesion','SS')->where('estatus','A')->get();
                     DB::table('sesiones_app_usuarios')->where('cod_sesion',$activo = 'SS')->where('estatus','A')->update(array('cod_sesion' => $objeto_user[0]['id_sesiones_app_usuarios'].$ip));
    
                }else{
                    $val_sesion = [  
                            'cod_sesion' => 'SS',
                            'cod_usuario' => $objeto_user_validar[0]['cod_usuario'],
                            'estatus' => $activo = 'A',
                            'fecha_inicio' => $ip,
                            'hora_inicio' => $ip,
                            'dir_ip_client' => $ip];
                     DB::table('sesiones_app_usuarios')->insert([ $val_sesion ]);
                     $objeto_user = sesiones_app_usuarios::where('cod_sesion','SS')->where('estatus','A')->get();
                     DB::table('sesiones_app_usuarios')->where('cod_sesion',$activo = 'SS')->where('estatus','A')->update(array('cod_sesion' => $objeto_user[0]['id_sesiones_app_usuarios'].$ip));
                }
                
                $objeto_user = sesiones_app_usuarios::where('cod_usuario',$objeto_user_validar[0]['cod_usuario'])->where('estatus','A')->get();
                $objeto_dep = inv_depositos_control::where('cod_deposito',$objeto_user_validar[0]['cod_usuario'])->where('estatus','A')->get();
                $objeto_img = reg_perfil_usuario_img::where('cod_usuario',$objeto_user_validar[0]['cod_usuario'])->where('estatus','A')->get();
                
                if (count($objeto_user) > 0) {
                    $message = 'Welcome '.$objeto_user_validar[0]['nom_usuario'];
                    return response()-> json([
                        'usuario'=> $objeto_user_validar, 
                        'sesion' =>$objeto_user,
                        'deposito' => $objeto_dep,
                        'message'=>$message,
                        'img' => $objeto_img] ,200);
                }else{
                    $message = 'There are no open sessions for this user';
                    return response()-> json(['404'=>$message],404);
                }
            }else{
                $message = 'invalid credentials';
                return response()-> json(['message'=>$message],404);
            }
        } catch (\Throwable $th) {
            return response()-> json(['message'=>$th],404);
        }
    }
   
    public function getPerfilAdmin(){
        try {
           
            $data = $_POST;
            $objeto = DB::select('call getUsuarioOperador(
                                                        "'.$data['des_usuario'].'",
                                                        "'.$data['correo_usuario'].'",
                                                        "'.$data['pass_usuario'].'")');
               
            if (count($objeto) > 0) {
                if (intval($objeto[0]->xresul) > 0){
                    $message = 'This email already exists';
                    return view('welcome',compact('message'));
                }else{
                    $message = 'User uploaded successfully';
                    return view('mensaje',compact('message'));
                }
            }else{
                $message = 'Query error';
                return view('mensaje',compact('message'));
            }
            
        } catch (Exception $e) {
            return view('mensaje',compact('e'));
        }
    }
    public function getCorreoValido(Request $id){
        try {
           
            $data = $id->all();
            $objeto = DB::select('call getCorreoValido(
                                                        "'.$data['correo'].'")'
                                                    );
               
            if (count($objeto) > 0) {
                
                $code = Str::random(8);

                $regcode = reg_codenv::where('correo',$data['correo'])->first();

                if ($regcode)
                {
                    $regcode->cod_env = $code;
                    $regcode->save();
                }
                else{
                    $data = [
                        'cod_env' => $code,
                        'correo' => $data['correo']
                    ];
                    reg_codenv::create($data);

                }


                
                $correo = new controladorCorreo($code);
                Mail::to($data['correo'])->send($correo);
                return response()-> json(['resul'=>'1'],200);
            }else{
                return response()-> json(['resul'=>'0'],404);
            }
            
        } catch (Exception $e) {
            return view('mensaje',compact('e'));
        }
    }

    public function getCodEnv(Request $id){
        try {
            $output = new ConsoleOutput();
            
            $ENV = new controladorENV();
            $data = $id->all();
            $objeto = DB::select('call getCodEnv(
                                                        "'.$data['cod_env'].'",
                                                        "'.$data['correo'].'"
                                                        )'
                                                    );
            
            if (count($objeto) > 0) {
                
                $ENV->getEnvCorreo($objeto);
                    return response()-> json(['resul'=>'1'],200);
            }else{
                return response()-> json(['resul'=>'0'],404);
            }
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getCodEnvPass(Request $id){
        try {
           
            $data = $id->all();

            $verify = reg_codenv::where('correo',$data['correo'])->where('cod_env',$data['cod_env'])->first();

            if($verify){
                return response()-> json(['resul'=>'1'],200);
            }
            else{
                return response()-> json(['resul'=>'0'],404);
            }


            /* $objeto = DB::select('call getCodEnvPass(
                                                        "'.$data['cod_env'].'",
                                                        "'.$data['correo'].'"
                                                        )'
                                                    );
               
            if (count($objeto) > 0) {
                    return response()-> json(['resul'=>'1'],200);
            }else{
                return response()-> json(['resul'=>'0'],404);
            } */
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getCodEnvPassUpdate(Request $id){
        try {
            $data = $id->all();

            $user = sk_usuarios::where('email',$data['correo'])->first();
            $user->password = bcrypt($data['pass_usuario_p']);
            $user->save();

            return response()-> json(['resul'=>'1'],200);


           
            /* $objeto = DB::select('call getCodEnvPassUpdate(
                                                        "'.$data['pass_usuario_p'].'",
                                                        "'.$data['correo'].'"
                                                        )'
                                                    );
               
            if (count($objeto) > 0) {
                    return response()-> json(['resul'=>'1'],200);
            }else{
                return response()-> json(['resul'=>'0'],404);
            } */
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }


}

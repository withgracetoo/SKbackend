<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Files;
use Illuminate\Support\Facades\Log;
use App\Models\usuario;
use App\Models\sweish_user;
use App\Models\sk_usuarios;
use App\Models\reg_perfil_usuario_img;
use App\Models\relacion_producto_img;
use App\Models\inv_depositos_control;
use App\Models\relacion_deposito_productos;
use App\Models\relacion_definiciones_sistema;
use App\Models\relacion_producto_img_galeria;
use App\Models\reg_cod;
use App\Http\Controllers\controladorHelpers;
use App\Mail\controladorContacto;
use App\Mail\controladorVerifymail;
use DB;
use Carbon\Carbon;
use DateTime;
use Torann\GeoIP\Facades\GeoIP;
use Illuminate\Support\Str;

class controladorUsuarios extends Controller
{
    public function getUsuario(Request $id){
        try {
            return response()-> json(sweish_user::all(),200);
        } catch (\Throwable $th) {
            return response()-> json(['try_catch'=>$th],404);
        }
    }

    /* public function getUsuarioSwiper(Request $id){
        try {
            $data = $id->all();
            $img;
            $objeto = sk_usuarios::where('estatus','A')->where('tipo','DLR')->get();
            $objeto_img = reg_perfil_usuario_img::where('estatus','A')->get();
            if (count($objeto_img) > 0) {
                $img = $objeto_img;
            }else{
                $img = [];
            }
            if (count($objeto) > 0) {
                return response()-> json(['usuario'=>$objeto,'img'=>$img] ,200);
            }else{
                $message = 'error al consultar';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (\Throwable $th) {
            return response()-> json(['try_catch'=>$th],404);
        }
    } */

    public function getUsuarioParametro(Request $id){
        try {
            $data = $id->all();
           /*  $objeto = sk_usuarios::where('cod_usuario',$data['cod_usuario'])->where('estatus','A')->get();
            $objeto_img = reg_perfil_usuario_img::where('cod_usuario',$data['cod_usuario'])->where('estatus','A')->get(); */
            $objeto = DB::select('call verusuario("'.$data['cod_usuario'].'")');
            if (count($objeto) > 0) {
                $objeto_basic = DB::select('call getVendedorAlmacen(
                                                                    "'.$data['cod_usuario'].'",
                                                                    "'.$data['cod_deposito'].'"
                                                                    )');
                return response()-> json(['usuario'=>$objeto,'vendedor'=>$objeto_basic] ,200);
            }else{
                $message = 'error al consultar';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getUsuarioParametroIdCloud(Request $id){
        try {
            $data = $id->all();
            $img;
            $objeto = sk_usuarios::where('id_cloud',$data['id_cloud'])->where('estatus','A')->get();
            $objeto_img = reg_perfil_usuario_img::where('cod_usuario',$objeto[0] ['cod_usuario'])->where('estatus','A')->get();
            if (count($objeto_img) > 0) {
                $img = $objeto_img;
            }else{
                $img = [];
            }
            if (count($objeto) > 0) {
                return response()-> json(['usuario'=>$objeto,'img'=>$img] ,200);
            }else{
                $message = 'error al consultar';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (\Throwable $th) {
            return response()-> json(['message'=>$th],404);
        }
    }
    public function getCrearMiembros(Request $id){
        Log::error('Entra al método');
    
        try {
            $ip = $_SERVER['REMOTE_ADDR'];
            $data = $id->all();
            $objeto_user_validar = sk_usuarios::where('email',$data['correo'])->where('estatus','A')->get();
            $objeto_nombre_validar = sk_usuarios::where('nom_usuario',$data['nom_usuario'])->where('estatus','A')->get();
            $objeto_definicion = relacion_definiciones_sistema::where('concepto_definicion',strtolower($data['categoria']))->get();
            if (count($objeto_nombre_validar) > 0) {
                $message = 'The user with the name '.$data['nom_usuario'].' already exists';
                $status = 'exist';
                return response()-> json(['message'=>$message, 'status'=>$status],200);
            }
            if (count($objeto_user_validar) > 0) {
                $message = 'The user with the email '.$data['correo'].' already exists';
                $status = 'exist';
                return response()-> json(['message'=>$message, 'status'=>$status],200);
            }else{
                log::info('paso las validaciones');
                $hash = md5( rand(0,1000) );

                $val_usuario = [  
                                'cod_usuario' => 'PID',
                                'estatus' => 'A',
                                'tipo' => $data['tipo'],
                                'email' => $data['correo'],
                                'categoria' => $data['categoria'],
                                'nom_usuario' => $data['nom_usuario'],
                                'id_cloud' => Str::uuid(),
                                'cod_definicion' => $objeto_definicion[0]-> cod_definicion,
                                'cod_concepto' => $objeto_definicion[0]-> cod_concepto,
                                'val_definicion' => $objeto_definicion[0]-> val_definicion,
                                'password' => bcrypt($data['pass']),
                                'pass_usuario' => $data['pass'],
                                'pass_usuario_p' => $data['pass'],
                                'hash' => $hash
                            ];
                DB::table('sk_usuarios')->insert([ $val_usuario ]);
                $objeto_user = sk_usuarios::where('cod_usuario','PID')->where('estatus','A')->get();
                DB::table('sk_usuarios')->where('cod_usuario','PID')->update(array('cod_usuario' => $objeto_user[0]['id'].$ip));
                $message = 'The user  '.$data['correo'].' successfully created';
                if (strtolower($data['tipo'])  === strtolower('DLR') ) {
                    $val_dep = [  
                        'cod_deposito' => $objeto_user[0]['id'].$ip,
                        'des_deposito' => $data['tipo'],
                        'tipo_deposito' => $data['tipo'],
                        'cod_usuario' => $objeto_user[0]['id'].$ip,
                        'estatus' => 'A',
                        'fecha' => '',
                        'hora' => ''];
                DB::table('inv_depositos_control')->insert([ $val_dep ]);
                }

                log::info('Se va a verificar el correo');

            
                $objeto_correo = sk_usuarios::where('email',$data['correo'])->where('estatus','A')->get();

                $useremail = trim($data['correo']);

                $correo = new controladorVerifymail($useremail, $hash);

                log::info('Se va a enviar el correo');
                Mail::to($useremail)->send($correo);

                return response()-> json(['correo'=>$objeto_correo,'message'=>$message,200]);
            }

        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }

    public function verifyMail($email, $hash)
    {


        $usuario = sk_usuarios::where('email',$email)
        ->where('hash',$hash)
        ->where('active',0)->first();

        if ($usuario){
            $usuario->active = 1;
            $usuario->save();
            return redirect()->away('https://swedishknickers.com');
            
        }
        else{
            return redirect()->away('https://swedishknickers.com');

        }

    }
    public function getImgPerfil(Request $id){
        try {
            $ip = $_SERVER['REMOTE_ADDR'];
            $data = $id->all();
            $objeto_user_validar = reg_perfil_usuario_img::where('cod_usuario',$data['cod_usuario'])->where('estatus','A')->get();
            if (count($objeto_user_validar) > 0) {
                DB::table('reg_perfil_usuario_img')->where('cod_usuario',$data['cod_usuario'])->
                where('estatus','A')->
                update(array('estatus' => 'I'));
                $val_img = [  
                                'cod_usuario' => $data['cod_usuario'],
                                'estatus' => 'A',
                                'cod_img' => $data['cod_img'],
                                'fecha_inicio' => '',
                                'hora_inicio' => '',
                ];
                DB::table('reg_perfil_usuario_img')->insert([ $val_img ]);
                $objeto_img = reg_perfil_usuario_img::where('cod_usuario',$data['cod_usuario'])->where('estatus','A')->get();
                $message = 'La carga de la imagen se realizo de forma exitosa';
                return response()-> json(['img'=> $objeto_img,'message'=>$message,200]);
            }else{
               $val_img = [  
                                'cod_usuario' => $data['cod_usuario'],
                                'estatus' => 'A',
                                'cod_img' => $data['cod_img'],
                                'fecha_inicio' => '',
                                'hora_inicio' => '',
                ];
                DB::table('reg_perfil_usuario_img')->insert([ $val_img ]);
                $objeto_img = reg_perfil_usuario_img::where('cod_usuario',$data['cod_usuario'])->where('estatus','A')->get();
                $message = 'La carga de la imagen se realizo de forma exitosa';
                return response()-> json(['img'=> $objeto_img,'message'=>$message,200]);
            }          
        } catch (\Throwable $th) {
            return response()-> json(['message'=>$th],404);
        }
    }
    public function getImgProd(Request $id){
        try {
            $ip = $_SERVER['REMOTE_ADDR'];
            $data = $id->all();
            $val_code = [  
                    'cod_producto' => 'PID',
                ];
                $validar_img_dlr = reg_perfil_usuario_img::where('cod_usuario',$data['cod_deposito'])->where('estatus','A')->get();
                if (count($validar_img_dlr) <= 0) {
                    $message='You must upload an image on your profile to be able to create a product';
                    return response()-> json(['message'=>$message],404);
                }
                DB::table('reg_cod')->insert([ $val_code ]);
                $objeto_code = reg_cod::where('cod_producto','PID')->get();
                $objeto_code_prod = $data['cod_deposito'].$objeto_code[0]['id_reg_cod'];
                $objeto_img_validar = relacion_producto_img::where('cod_producto',$data['cod_producto'])->
                                                             where('cod_deposito',$data['cod_deposito'])->
                                                             where('estatus','A')->
                                                             get();
            $objeto_dep_validar = inv_depositos_control::where('cod_deposito',$data['cod_deposito'])->where('estatus','A')->get();
            if (count($objeto_img_validar) > 0 && count($objeto_dep_validar) > 0) {
                DB::table('relacion_producto_img')->where('cod_deposito',$data['cod_deposito'])->
                where('cod_producto',$data['cod_deposito'])->
                where('estatus','A')->
                update(array('estatus' => 'I'));

                $val_prod = [  
                                'cod_deposito' => $data['cod_deposito'],
                                'cod_producto' => $objeto_code_prod,
                                'des_producto' => $data['des_producto'],
                                'cant_producto' => $data['cant_producto'],
                                'estatus' => 'A',
                                'um_producto' => $data['um_producto'],
                                'fecha_inicio' => '',
                                'cat_producto' => $data['cat_producto'],
                                'hora_inicio' => '',
                ];
                DB::table('relacion_deposito_productos')->insert([ $val_prod ]);

                $val_img = [  
                                'cod_usuario' => $data['cod_usuario'],
                                'cod_deposito' => $data['cod_deposito'],
                                'cod_producto' => $objeto_code_prod,
                                'cod_img' => $data['cod_img'],
                                'estatus' => 'A',
                                'fecha_inicio' => '',
                                'hora_inicio' => '',
                ];
                DB::table('relacion_producto_img')->insert([ $val_img ]);
                $message = 'La carga de la imagen se realizo de forma exitosa';
                DB::table('reg_cod')->where('cod_producto','PID')->update(array('cod_producto' => $objeto_code_prod));
                return response()-> json(['message'=>$message,200]);
            }else{
                $val_prod = [  
                                'cod_deposito' => $data['cod_deposito'],
                                'cod_producto' => $objeto_code_prod,
                                'des_producto' => $data['des_producto'],
                                'cant_producto' => $data['cant_producto'],
                                'estatus' => 'A',
                                'um_producto' => $data['precio_producto'],
                                'tipo_producto' => $data['tipo_producto'],
                                'cat_producto' => $data['cat_producto'],
                                'fecha_inicio' => '',
                                'hora_inicio' => '',
                ];
                DB::table('relacion_deposito_productos')->insert([ $val_prod ]);
               $val_img = [  
                                'cod_usuario' => $data['cod_usuario'],
                                'cod_deposito' => $data['cod_deposito'],
                                'cod_producto' => $objeto_code_prod,
                                'cod_img' => $data['cod_img'],
                                'estatus' => 'A',
                                'fecha_inicio' => '',
                                'hora_inicio' => '',
                ];
                DB::table('relacion_producto_img')->insert([ $val_img ]);
                $message = 'Producto '.$objeto_code_prod.' fue creado de forma exitosa';
                DB::table('reg_cod')->where('cod_producto','PID')->update(array('cod_producto' => $objeto_code_prod));
                return response()-> json(['message'=>$message,200]);
            }          
        } catch (\Throwable $th) {
            return response()-> json(['message'=>$th],404);
        }
    }


    public function getImgProducto(Request $request){

        try {

            if ($request->hasfile('imagen')){

                $imagen = $request->file('imagen');

                $nombre = time().'_'.$imagen->getClientOriginalName();
                $ruta = public_path().'/images/products/main';
                $imagen->move($ruta,$nombre);
                $url_img = '/images/products/main/' . $nombre;
                
                Log::info('Pasa sin problema');

                //return $url_img;
            }

            $helper = new controladorHelpers();
            $ip = $_SERVER['REMOTE_ADDR'];
            $data = $request->all();
            $data['cod_img'] = $url_img;
            if (intval($data['operacion']) > 0) {
              
                $objeto = DB::select('call getActualizarProductoVendedor(
                    "'.$data['cod_usuario'].'",
                    "'.$data['cod_producto'].'",
                    "'.$data['cod_deposito'].'",
                    "'.$data['des_producto'].'",
                    "'.$data['precio_producto'].'",
                    "'.$data['tipo_producto'].'",
                    "'.$url_img.'"
                    )');

                    if (count($objeto) > 0) {
                        $message = 'Product updated successfully';
                    return response()-> json(['message'=>$message],200);
                    
                }else{
                    $message = 'Query error';
                    return response()-> json(['message'=>$message],404);
                }
            }else{
                $val_code = [  
                    'cod_producto' => 'PID',
                ];
                $validar_img_dlr = reg_perfil_usuario_img::where('cod_usuario',$data['cod_usuario'])->where('estatus','A')->get();
                if (count($validar_img_dlr) <= 0) {
                    $message='You must upload an image on your profile to be able to create a product';
                    return response()-> json(['message'=>$message],404);
                }
                DB::table('reg_cod')->insert([ $val_code ]);
                $objeto_code = reg_cod::where('cod_producto','PID')->get();
                $objeto_code_prod = $data['cod_deposito'].$objeto_code[0]['id_reg_cod'];
                $objeto_img_validar = relacion_producto_img::where('cod_producto',$data['cod_producto'])->
                                                             where('cod_deposito',$data['cod_deposito'])->
                                                             where('estatus','A')->
                                                             get();
            $objeto_dep_validar = inv_depositos_control::where('cod_deposito',$data['cod_deposito'])->where('estatus','A')->get();
            $objeto_dep_validar_t = inv_depositos_control::where('cod_deposito',$data['cod_deposito'])->where('estatus','T')->get();

            /* return response()-> json(['dep1'=>count($objeto_dep_validar),'dep2'=>count($objeto_dep_validar_t)],404); */
            if (count($objeto_dep_validar) > 0) {
                if (count($objeto_img_validar) > 0 && count($objeto_dep_validar) > 0) {
                    DB::table('relacion_producto_img')->where('cod_deposito',$data['cod_deposito'])->
                    where('cod_producto',$data['cod_deposito'])->
                    where('estatus','A')->
                    update(array('estatus' => 'I'));
    
                    $val_prod = [  
                                    'cod_deposito' => $data['cod_deposito'],
                                    'cod_producto' => $objeto_code_prod,
                                    'des_producto' => $data['des_producto'],
                                    'cant_producto' => $data['cant_producto'],
                                    'estatus' => 'A',
                                    'um_producto' => $data['precio_producto'],
                                    'tipo_producto' => $data['tipo_producto'],
                                    'fecha_inicio' => '',
                                    'cat_producto' => $data['cat_producto'],
                                    'hora_inicio' => '',
                    ];
                    DB::table('relacion_deposito_productos')->insert([ $val_prod ]);
    
                    $val_img = [  
                                    'cod_usuario' => $data['cod_usuario'],
                                    'cod_deposito' => $data['cod_deposito'],
                                    'cod_producto' => $objeto_code_prod,
                                    'cod_img' => $data['cod_img'],
                                    'estatus' => 'A',
                                    'fecha_inicio' => '',
                                    'hora_inicio' => '',
                    ];
                    DB::table('relacion_producto_img')->insert([ $val_img ]);
                    $message = 'The upload of the image was done successfully';
                    DB::table('reg_cod')->where('cod_producto','PID')->update(array('cod_producto' => $objeto_code_prod));
                    return response()-> json(['message'=>$message,200]);
                }else{
                    $val_prod = [  
                                    'cod_deposito' => $data['cod_deposito'],
                                    'cod_producto' => $objeto_code_prod,
                                    'des_producto' => $data['des_producto'],
                                    'cant_producto' => $data['cant_producto'],
                                    'estatus' => 'A',
                                    'um_producto' => $data['precio_producto'],
                                    'tipo_producto' => $data['tipo_producto'],
                                    'cat_producto' => $data['cat_producto'],
                                    'fecha_inicio' => '',
                                    'hora_inicio' => '',
                    ];
                    DB::table('relacion_deposito_productos')->insert([ $val_prod ]);
                   $val_img = [  
                                    'cod_usuario' => $data['cod_usuario'],
                                    'cod_deposito' => $data['cod_deposito'],
                                    'cod_producto' => $objeto_code_prod,
                                    'cod_img' => $data['cod_img'],
                                    'estatus' => 'A',
                                    'fecha_inicio' => '',
                                    'hora_inicio' => '',
                    ];
                    DB::table('relacion_producto_img')->insert([ $val_img ]);

                    /////SEGUIMIENTO DE OPERACION CREAR PRODUCTO/////
                    DB::select('call getSegOperProd(
                                                    "'.$helper->CREAR.'",
                                                    "",
                                                    "'.$data['cod_deposito'].'",
                                                    "'.$objeto_code_prod.'",
                                                    "'.$data['cod_img'].'",
                                                    "'.$data['des_producto'].'",
                                                    "'.$data['precio_producto'].'",
                                                    "'.$data['cat_producto'].'",
                                                    "",
                                                    "",
                                                    "A"
                                                    )');
                    $message = 'Product '.$objeto_code_prod.' was successfully created';
                    DB::table('reg_cod')->where('cod_producto','PID')->update(array('cod_producto' => $objeto_code_prod));
                    return response()-> json(['message'=>$message,200]);
                } 
            }else{
                if (count($objeto_img_validar) > 0 && count($objeto_dep_validar_t) > 0) {
             
                    DB::table('relacion_producto_img')->where('cod_deposito',$data['cod_deposito'])->
                    where('cod_producto',$data['cod_deposito'])->
                    where('estatus','A')->
                    update(array('estatus' => 'I'));
    
                    $val_prod = [  
                                    'cod_deposito' => $data['cod_deposito'],
                                    'cod_producto' => $objeto_code_prod,
                                    'des_producto' => $data['des_producto'],
                                    'cant_producto' => $data['cant_producto'],
                                    'estatus' => 'A',
                                    'um_producto' => $data['precio_producto'],
                                    'tipo_producto' => $data['tipo_producto'],
                                    'fecha_inicio' => '',
                                    'cat_producto' => $data['cat_producto'],
                                    'hora_inicio' => '',
                    ];
                    DB::table('relacion_deposito_productos')->insert([ $val_prod ]);
    
                     $val_prod_t = [  
                                    'cod_prdr' => $objeto_dep_validar_t[0]->cod_prdr,
                                    'cod_deposito' => $data['cod_deposito'],
                                    'cod_producto' => $objeto_code_prod,
                                    'des_producto' => $data['des_producto'],
                                    'cant_producto' => $data['cant_producto'],
                                    'des_deposito' => '',
                                    'estatus' => 'A',
                                    'um_producto' => $data['precio_producto'],
                                    'tipo_producto' => $data['tipo_producto'],
                                    'fecha_inicio' => '',
                                    'cat_producto' => $data['cat_producto'],
                                    'hora_inicio' => '',
                    ];

                    DB::table('relacion_deposito_productos_operador')->insert([ $val_prod_t ]);
    
                    $val_img = [  
                                    'cod_usuario' => $data['cod_usuario'],
                                    'cod_deposito' => $data['cod_deposito'],
                                    'cod_producto' => $objeto_code_prod,
                                    'cod_img' => $data['cod_img'],
                                    'estatus' => 'A',
                                    'fecha_inicio' => '',
                                    'hora_inicio' => '',
                    ];
                    DB::table('relacion_producto_img')->insert([ $val_img ]);
                    $message = 'The upload of the image was done successfully';
                    
                    DB::table('reg_cod')->where('cod_producto','PID')->update(array('cod_producto' => $objeto_code_prod));
                    return response()-> json(['message'=>$message,200]);
                }else{
                    $val_prod = [  
                                    'cod_deposito' => $data['cod_deposito'],
                                    'cod_producto' => $objeto_code_prod,
                                    'des_producto' => $data['des_producto'],
                                    'cant_producto' => $data['cant_producto'],
                                    'estatus' => 'A',
                                    'um_producto' => $data['precio_producto'],
                                    'tipo_producto' => $data['tipo_producto'],
                                    'cat_producto' => $data['cat_producto'],
                                    'fecha_inicio' => '',
                                    'hora_inicio' => '',
                    ];
            
                    DB::table('relacion_deposito_productos')->insert([ $val_prod ]);
    
                    $val_prod_t = [  
                                    'cod_prdr' => $objeto_dep_validar_t[0]->cod_prdr,
                                    'cod_deposito' => $data['cod_deposito'],
                                    'cod_producto' => $objeto_code_prod,
                                    'des_producto' => $data['des_producto'],
                                    'cant_producto' => $data['cant_producto'],
                                    'des_deposito' => '',
                                    'estatus' => 'A',
                                    'um_producto' => $data['precio_producto'],
                                    'tipo_producto' => $data['tipo_producto'],
                                    'fecha_inicio' => '',
                                    'cat_producto' => $data['cat_producto'],
                                    'hora_inicio' => '',
                    ];
                    Log::info('Inserta en relacion_deposito_productos_operador');

                    DB::table('relacion_deposito_productos_operador')->insert([ $val_prod_t ]);
                   $val_img = [  
                                    'cod_usuario' => $data['cod_usuario'],
                                    'cod_deposito' => $data['cod_deposito'],
                                    'cod_producto' => $objeto_code_prod,
                                    'cod_img' => $data['cod_img'],
                                    'estatus' => 'A',
                                    'fecha_inicio' => '',
                                    'hora_inicio' => '',
                    ];
                    DB::table('relacion_producto_img')->insert([ $val_img ]);
                    /////SEGUIMIENTO DE OPERACION CREAR PRODUCTO/////
                    DB::select('call getSegOperProd(
                                                    "'.$helper->CREAR.'",
                                                    "",
                                                    "'.$data['cod_deposito'].'",
                                                    "'.$objeto_code_prod.'",
                                                    "'.$data['cod_img'].'",
                                                    "'.$data['des_producto'].'",
                                                    "'.$data['precio_producto'].'",
                                                    "'.$data['cat_producto'].'",
                                                    "",
                                                    "",
                                                    "A"
                                                    )');
                    $message = 'Product '.$data['des_producto'].' was successfully created';
                    DB::table('reg_cod')->where('cod_producto','PID')->update(array('cod_producto' => $objeto_code_prod));
                    return response()-> json(['message'=>$message,200]);
                } 
            }  
            }
                     
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }


    public function getImgDLR(Request $id){
        try {
            $ip = $_SERVER['REMOTE_ADDR'];
            $data = $id->all();
                /* $objeto_img = reg_perfil_usuario_img::where('cod_usuario',$data['cod_usuario'])->where('estatus','A')->get();
                if (count($objeto_img) <= 0) {
                    $objeto_img = '';
                }
                $objeto_dlr = sk_usuarios::where('cod_usuario',$data['cod_usuario'])->where('estatus','A')->get(); */
                $objeto = DB::select('call getImgDLR("'.$data['cod_prdr'].'")');
                if (count($objeto) > 0) {
                    return response()-> json(['dlr'=> $objeto,200]);
                }else{
                    $message ='Error when consulting the seller';
                    return response()-> json(['message'=> $message,404]);
                }
                
        } catch (Exception $th) {
            return response()-> json(['message'=>$th],404);
        }
    }
    public function getUsuarioSwiper(Request $id){
        try {
            $data = $id->all();
            $arry = [];
        /* $objeto = DB::select('call verusuarioDLR("'.$data['cod_usuario'].'")'); */
        $objeto = DB::select('call verusuarioDLR()');
        if (count($objeto) > 0) {
            foreach ($objeto as  $value) {
                $objeto_prdr = DB::select('call getPrdrDep("'.$value->cod_usuario.'")');
                if (count($objeto_prdr) > 0) {
                    array_push($arry, $value);
                }
            }
            return response()-> json(['usuario'=> $arry,200]);
        }else{
            $message = 'Query error';
            return response()-> json(['message'=> $message,404]);
        }
        } catch (Exception $th) {
            return response()-> json(['message'=> $th,404]);
        }
    }
    public function getUsuarioSwiperSeller(Request $id){
        try {
            $data = $id->all();
        
        $objeto = DB::select('call verusuarioSeller()');
        if (count($objeto) > 0) {
            return response()-> json(['usuario'=> $objeto,200]);
        }else{
            $message = 'Query error';
            return response()-> json(['message'=> $message,404]);
        }
        } catch (Exception $th) {
            return response()-> json(['message'=> $th,404]);
        }
    }
    public function getUsuarioChat(Request $id){
        try {
            $data = $id->all();
           /*  $objeto = sk_usuarios::where('cod_usuario',$data['cod_usuario'])->where('estatus','A')->get();
            $objeto_img = reg_perfil_usuario_img::where('cod_usuario',$data['cod_usuario'])->where('estatus','A')->get(); */
            $objeto = DB::select('call verUsuarioContacto("'.$data['cod_usuario'].'")');
            if (count($objeto) > 0) {
                return response()-> json(['usuario'=>$objeto] ,200);
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (\Throwable $th) {
            return response()-> json(['message'=>$th],404);
        }
    }
    public function getUsuarioContactoVs(Request $id){
        try {
            $data = $id->all();
           /*  $objeto = sk_usuarios::where('cod_usuario',$data['cod_usuario'])->where('estatus','A')->get();
            $objeto_img = reg_perfil_usuario_img::where('cod_usuario',$data['cod_usuario'])->where('estatus','A')->get(); */
            $objeto = DB::select('call getUsuarioContactoVs("'.$data['cod_usuario'].'")');
            if (count($objeto) > 0) {
                return response()-> json(['usuario'=>$objeto] ,200);
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (\Throwable $th) {
            return response()-> json(['message'=>$th],404);
        }
    }
    public function getUpdateUsuario(Request $request){

        try {
            $data = $request->all();

            if ($request->hasfile('imagen')){

                $imagen = $request->file('imagen');

                $nombre = time().'_'.$imagen->getClientOriginalName();
                $ruta = public_path().'/images/profiles';
                $imagen->move($ruta,$nombre);
                $url_img = '/images/profiles/' . $nombre;
                
                Log::info('Pasa sin problema');

                //return $url_img;
            }
            else{
                $url_img = $data['cod_img'];
            }
            


           /*  $objeto = sk_usuarios::where('cod_usuario',$data['cod_usuario'])->where('estatus','A')->get();
            $objeto_img = reg_perfil_usuario_img::where('cod_usuario',$data['cod_usuario'])->where('estatus','A')->get(); */
           
            $objeto = DB::select('call updateusuario(
                                                        "'.$data['cod_usuario'].'",
                                                        "'.$data['des_usuario'].'",
                                                        "'.$data['ape_usuario'].'",
                                                        "'.$url_img.'",
                                                        "'.$data['descrip_usuario'].'",
                                                        "'.$data['edad_usuario'].'",
                                                        "'.$data['fecha_usuario'].'",
                                                        "'.$data['nom_usuario'].'",
                                                        "'.$data['pais_usuario'].'",
                                                        "'.$data['sex_usuario'].'",
                                                        "'.$data['bank_data'].'",
                                                        "'.$data['dni_usuario'].'",

                                                        "'.$data['dir_usuario'].'",
                                                        "'.$data['body_data'].'",
                                                        "'.$data['altura_usuario'].'",
                                                        "'.$data['color_usuario'].'",
                                                        "'.$data['ojos_usuario'].'",
                                                        "'.$data['pelo_usuario'].'",
                                                        "'.$data['fumar_usuario'].'",
                                                        "'.$data['comida_usuario'].'",
                                                        "'.$data['nino_usuario'].'",

                                                        "'.$data['bank_name'].'",
                                                        "'.$data['bank_swift'].'",
                                                        "'.$data['bank_paypal'].'"

                                                        )');
                
            if (count($objeto) > 0) {
                $message = 'User updated successfully';
                return response()-> json(['usuario'=>$objeto, 'message' => $message] ,200);
            }else{
                $message = 'query error';
                return response()-> json(['message'=>$objeto],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getUsuarioOperador(){
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
    public function getUpdateUsuarioOper(Request $id){
        try {
           
            $data = $id->all();
            $objeto = DB::select('call getUpdateUsuarioOper(
                                                        "'.$data['cod_usuario'].'",
                                                        "'.$data['id_cloud'].'")');
               
            if (count($objeto) > 0) {
                return response()-> json(['usuario'=>$objeto] ,200);
            }else{
                $message = 'query error';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getUpdateUsuarioVisit(Request $id){
        try {
           
            $data = $id->all();
            $objeto = DB::select('call getUpdateUsuarioVisit(
                                                        "'.$data['cod_usuario'].'",
                                                        "'.$data['id_cloud'].'")');
               
            if (count($objeto) > 0) {
                return response()-> json(['usuario'=>$objeto] ,200);
            }else{
                $message = 'query error';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getOperadorVendedor(Request $id){
        try {
           
            $data = $id->all();
            $arry = [];
            $arry_prod = [];
            $objeto = DB::select('call getOperadorVendedor("'.$data['cod_prdr'].'")');
               
            if (count($objeto) > 0) {
                foreach ($objeto as $i) {
                    $objeto_data = DB::select('call getDataVendedor("'.$i->cod_usuario.'")');
                    $objeto_prod = DB::select('call getDataVendedorProducto("'.$objeto_data[0]->cod_deposito.'")');
                    foreach ($objeto_prod as $b){
                        $b->nom_usuario = $objeto_data[0] -> nom_usuario;
                        $b->des_usuario = $objeto_data[0] -> des_usuario;
                        array_push($arry_prod,$b);
                    }
                    array_push($arry,$objeto_data[0]);
                }
                    $message = 'Sellers uploaded successfully';
                    return response()-> json(['message'=>$message,'vendor'=>$arry,'productos'=> $arry_prod],200);
            }else{
                $message = 'It has no associated vendors';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getActualizarMembresia(Request $id){
        try {
           
            $data = $id->all();
            $objeto = DB::select('call getActualizarMembresia(
                                                        "'.$data['cod_usuario'].'",
                                                        "'.$data['cod_definicion'].'",
                                                        "'.$data['categoria_usuario_actual'].'",
                                                        "'.$data['cod_concepto_actual'].'",
                                                        "'.$data['val_definicion_actual'].'",
                                                        "'.$data['categoria_usuario_cambio'].'",
                                                        "'.$data['cod_concepto_cambio'].'",
                                                        "'.$data['val_definicion_cambio'].'"
                                                        )');
               
            if (count($objeto) > 0) {
                if (intval($objeto[0]->resul) > 0){
                    $message = 'Membership update successful, please login again to apply the changes';
                    return response()-> json(['message'=>$message],200);
                }else{
                    $message = 'query error';
                    return response()-> json(['message'=>$message],404);
                } 
            }else{
                $message = 'query error';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getBilleteraVendedor(Request $id){
        try {
            $helper = new controladorHelpers();
            $um_disponible = 0;
            $um_espera = 0;
            $um_bloqueado = 0;
            $um_total = 0;
            
           
            $data = $id->all();
            $objeto = DB::select('call getBilleteraVendedor(
                                                        "'.$data['cod_usuario'].'"
                                                        )');
               
            if (count($objeto) > 0) {
                    $message = 'Wallet loaded successful';
                    return response()-> json(['message'=>$message,'valor'=> $objeto],200);
               
            }else{
                $objeto_billetera = DB::select('call getCrearBilleteraVendedor(
                                                                "'.$data['cod_usuario'].'",
                                                                "'.$data['cod_usuario'].'"
                                                                )');
                                                                $objeto_valores = DB::select('call getValorBilleteraVendedor(
                                                                    "'.$data['cod_deposito'].'",
                                                                    "'.$data['cod_deposito'].'"
                                                                    )');
                    $objeto_espera = DB::select('call getValorBilleteraEsperaVendedor(
                                                                    "'.$data['cod_deposito'].'",
                                                                    "'.$data['cod_deposito'].'"
                                                                    )');
                    $objeto_valor_dispo = DB::select('call getValorDispoBilleteraVendedor(
                                                                    "'.$data['cod_deposito'].'",
                                                                    "'.$data['cod_deposito'].'"
                                                                    )');
    
                    foreach ($objeto_valores as $value) {
                        if ($value->um_producto === null) {
                            $value->um_producto = '0';
                        }
                        $um_bloqueado = $um_bloqueado + floatval($value->um_producto);
                    }
                    foreach ($objeto_espera as $value) {
                        if ($value->um_producto === null) {
                            $value->um_producto = '0';
                        }
                        $um_espera = $um_espera + floatval($value->um_producto);
                    }
                    foreach ($objeto_valor_dispo as $value) {
                        if ($value->um_producto === null) {
                            $value->um_producto = '0';
                        }
                        $um_disponible = $um_disponible + floatval($value->um_producto);
                    }
                    $um_total = $um_disponible + $um_espera + $um_bloqueado;
                
                if ($um_total > 0) {
                    $objeto_valor_dispo = DB::select('call getActualizarBilleteraVendedor(
                                                                "'.$data['cod_usuario'].'",
                                                                "'.$data['cod_usuario'].'",
                                                                "'.$objeto_billetera[0]->um_disponible.'",
                                                                "'.$um_disponible.'",
                                                                "'.$objeto_billetera[0]->um_espera.'",
                                                                "'.$um_espera.'",
                                                                "'.$objeto_billetera[0]->um_bloqueado.'",
                                                                "'.$um_bloqueado.'",
                                                                "'.$objeto_billetera[0]->um_total.'",
                                                                "'.$um_total.'"
                                                                )');
                    $message = 'Wallet loaded successful';
                    return response()-> json(['message'=>$message,'valor'=> $objeto_valor_dispo],200);
                }else{
                    $message = 'Wallet loaded successful';
                    return response()-> json(['message'=>$message,'valor'=> $objeto_billetera],200);
                }
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getDataUsuario(Request $id){
        try {
            $helper = new controladorHelpers();
        
            $data = $id->all();
            $objeto = DB::select('call getDataUsuario(
                                                        "'.$data['cod_usuario'].'"
                                                        )');
               
            if (count($objeto) > 0) {
                    $message = 'Data loaded successful';
                    return response()-> json(['message'=>$message,'usuario'=> $objeto],200);
               
            }else{
                    $message = 'Data does not exist';
                    return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getPrdrIdcloud(Request $id){
        try {
            $helper = new controladorHelpers();
        
            $data = $id->all();
            $objeto = DB::select('call getPrdrIdcloud(
                                                        "'.$data['cod_prdr'].'"
                                                        )');
            $objeto_usuario = DB::select('call getDataUsuario(
                                                        "'.$data['cod_deposito'].'"
                                                        )');
               
            if (count($objeto) > 0 && count($objeto_usuario) > 0) {
                    $message = 'Data loaded successful';
                    return response()-> json(['message'=>$message,'prdr'=> $objeto, 'dlr'=>$objeto_usuario],200);
               
            }else{
                    $message = 'Data does not exist';
                    return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getUpdateIdcloud(Request $id){
        try {
            $helper = new controladorHelpers();
        
            $data = $id->all();
            $objeto = DB::select('call getUpdateIdcloud(
                                                        "'.$data['correo'].'",
                                                        "'.$data['id_cloud'].'"
                                                        )');
               
            if (count($objeto) > 0) {
                    $message = 'Data loaded successful';
                    return response()-> json(['message'=>$message],200);
               
            }else{
                    $message = 'Data does not exist';
                    return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getQuitarFavoritos(Request $id){
        try {
            $helper = new controladorHelpers();
        
            $data = $id->all();
            $objeto = DB::select('call getQuitarFavoritos(
                                                        "'.$data['cod_dlr'].'",
                                                        "'.$data['cod_mmbr'].'"
                                                        )');
               
            if (count($objeto) > 0) {
                    $message = 'Data delete successful';
                    return response()-> json(['message'=>$message],200);
               
            }else{
                    $message = 'Data does not exist';
                    return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getCategoriaUsuario(Request $id){
        try {
            $helper = new controladorHelpers();
        
            $data = $id->all();
            $objeto = DB::select('call getCategoriaUsuario(
                                                        "'.$data['cod_definicion'].'",
                                                        "'.$data['cod_concepto'].'"
                                                        )');
               
            if (count($objeto) > 0) {
                    return response()-> json(['categoria'=>$objeto],200);
               
            }else{
                    $message = 'Data does not exist';
                    return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getMsmUsuario(Request $id){
        try {
            $helper = new controladorHelpers();
        
            $data = $id->all();
            $objeto = DB::select('call getMsmUsuario(
                                                        "'.$data['cod_usuario'].'",
                                                        "'.$data['msm_usuario'].'",
                                                        "'.$data['chat_usuario'].'",
                                                        "'.$data['deseo_usuario'].'",
                                                        "'.$data['swipe_usuario'].'"
                                                        )');
               
           
            return response()-> json(['msm'=>$objeto],200);
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getMsmUsuarioCont(Request $id){
        try {
            $helper = new controladorHelpers();
        
            $data = $id->all();
            $objeto = DB::select('call getMsmUsuarioCont(
                                                        "'.$data['cod_usuario'].'",
                                                        "'.$data['msm_usuario'].'",
                                                        "'.$data['chat_usuario'].'",
                                                        "'.$data['deseo_usuario'].'",
                                                        "'.$data['swipe_usuario'].'"
                                                        )');
               
            if (count($objeto) > 0) {
                    return response()-> json(['resul'=>$objeto],200);
               
            }else{
                    $message = 'Data does not exist';
                    return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getEliminarPefil(Request $id){
        try {
            $helper = new controladorHelpers();
        
            $data = $id->all();
            $objeto = DB::select('call getEliminarPefil(
                                                        "'.$data['cod_usuario'].'"
                                                        )');
               
            if (count($objeto) > 0) {
                    return response()-> json(['resul'=>$objeto],200);
               
            }else{
                    $message = 'Data does not exist';
                    return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getImgProductoGaleria(Request $id){

        try {

            if ($id->hasfile('imagen')){

                $imagen = $id->file('imagen');

                $nombre = time().'_'.$imagen->getClientOriginalName();
                $ruta = public_path().'/images/products/gallery';
                $imagen->move($ruta,$nombre);
                $url_img = '/images/products/gallery/' . $nombre;

            }

            $helper = new controladorHelpers();
        
            $data = $id->all();
            $objeto = DB::select('call getImgProductoGaleria(
                                                "'.$data['cod_usuario'].'",
                                                "'.$data['cod_producto'].'",
                                                "'.$data['cod_deposito'].'",
                                                "'.$url_img.'"
                                                )');

                if (count($objeto) > 0) {
                    $message = 'Image loaded successfully';
                return response()-> json(['message'=>$message],200);
                
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getGaleria(Request $id){
        try {
            $helper = new controladorHelpers();
        
            $data = $id->all();
            $objeto = DB::select('call getGaleria(
                                                "'.$data['cod_usuario'].'",
                                                "'.$data['cod_producto'].'"
                                                )');

            if (count($objeto) > 0) {
                return response()-> json(['img'=>$objeto],200);
                
            }else{
                $message = 'No data in Gallery';
                return response()-> json(['message'=>$message],200);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getQuitarGaleria(Request $id){
        try {
            $helper = new controladorHelpers();
        
            $data = $id->all();

            $id =  $id->id;

            $res = relacion_producto_img_galeria::findOrFail($id);
    
            $res->delete();

            $message = 'Image deleted successfully';
            return response()-> json(['message'=>$message],200);


            /* $objeto = DB::select('call getQuitarGaleria(
                                                "'.$data['cod_usuario'].'",
                                                "'.$data['cod_producto'].'",
                                                "'.$data['cod_img'].'"
                                                )');

                if (count($objeto) > 0) {
                    $message = 'Image deleted successfully';
                return response()-> json(['message'=>$message],200);
                
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$message],404);
            } */
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getQuitarProducto(Request $id){
        try {
            $helper = new controladorHelpers();
        
            $data = $id->all();
            $objeto = DB::select('call getQuitarProducto(
                                                "'.$data['cod_producto'].'",
                                                "'.$data['cod_deposito'].'"
                                                )');

                if (count($objeto) > 0) {
                    $message = 'Product deleted successfully';
                return response()-> json(['message'=>$message],200);
                
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getImgPerfilGaleria(Request $id){
        try {
            $helper = new controladorHelpers();

            if ($id->hasfile('cod_img')){
                $imagen = $id->file('cod_img');
                $nombre = time().'_'.$imagen->getClientOriginalName();
                $ruta = public_path().'/images/profiles/gallery';
                $imagen->move($ruta,$nombre);
                $url_img = '/images/profiles/gallery/' . $nombre;
                
                Log::info('Pasa sin problema');

            }
        
            $data = $id->all();
            $objeto = DB::select('call getImgPerfilGaleria(
                                                "'.$data['cod_usuario'].'",
                                                "'.$url_img.'"
                                                )');

                if (count($objeto) > 0) {
                    $message = 'Image loaded successfully';
                return response()-> json(['message'=>$message],200);
                
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getGaleriaPerfil(Request $id){
        try {
            $helper = new controladorHelpers();
        
            $data = $id->all();
            $objeto = DB::select('call getGaleriaPerfil(
                                                "'.$data['cod_usuario'].'"
                                                )');

                if (count($objeto) > 0) {
                return response()-> json(['img'=>$objeto],200);
                
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getQuitarGaleriaPerfil(Request $id){
        try {
            $helper = new controladorHelpers();
        
            $data = $id->all();

            DB::table('ralacion_perfil_img_galeria')->where('id_ralacion_producto_img_galeria',$data['id'])->delete();

            $message = 'Image deleted successfully';
            return response()-> json(['message'=>$message],200);


            $objeto = DB::select('call getQuitarGaleriaPerfil(
                                                "'.$data['cod_usuario'].'",
                                                "'.$data['cod_img'].'"
                                                )');

/*                 if (count($objeto) > 0) {
                    $message = 'Image deleted successfully';
                return response()-> json(['message'=>$message],200);
                
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$message],404);
            }
             */
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getLimitesUpdate(Request $id){
        try {
            $helper = new controladorHelpers();
            $usuarios_mmbr =  DB::select('call getUsuarioMiembro()');
           $fecha =  str_replace ( '/', '-', $usuarios_mmbr[0]->fecha_inicio);
            $inicio = new DateTime($fecha.' '.$usuarios_mmbr[0]->hora_inicio);
            $actual = new DateTime();
            $intervalo = $inicio->diff($actual);
            return response()-> json(['message'=>$intervalo, 'inicio'=>$inicio, 'actual'=>$actual],200);
            
        } catch (Exception $th) {
            return response()-> json(['message'=> $th,404]);
        }
    }
    public function getImgProductoVidGaleria(Request $id){
        try {
            $helper = new controladorHelpers();
        
            $data = $id->all();
            $objeto = DB::select('call getImgProductoVidGaleria(
                                                "'.$data['cod_usuario'].'",
                                                "'.$data['cod_producto'].'",
                                                "'.$data['cod_deposito'].'",
                                                "'.$data['cod_vid'].'"
                                                )');

                if (count($objeto) > 0) {
                    $message = 'Video loaded successfully';
                return response()-> json(['message'=>$message],200);
                
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getImgProductoVidGaleriaPerfil(Request $id){
        try {
            $helper = new controladorHelpers();
        
            $data = $id->all();
            $objeto = DB::select('call getImgProductoVidGaleriaPerfil(
                                                "'.$data['cod_usuario'].'",
                                                "'.$data['cod_vid'].'"
                                                )');

                if (count($objeto) > 0) {
                    $message = 'Video loaded successfully';
                return response()-> json(['message'=>$message],200);
                
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getQuitarGaleriaVid(Request $id){
        try {
            $helper = new controladorHelpers();
        
            $data = $id->all();
            $objeto = DB::select('call getQuitarGaleriaVid(
                                                "'.$data['cod_vid'].'"
                                                )');

                if (count($objeto) > 0) {
                return response()-> json(['message'=>$objeto],200);
                
            }else{
                $message = 'no videos available';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getVidGaleriaPerfil(Request $id){
        try {
            $helper = new controladorHelpers();
        
            $data = $id->all();
            $objeto = DB::select('call getVidGaleriaPerfil(
                                                "'.$data['cod_usuario'].'"
                                                )');

                if (count($objeto) > 0) {
                return response()-> json(['vid'=>$objeto],200);
                
            }else{
                $message = 'no videos available';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getVidGaleria(Request $id){
        try {
            $helper = new controladorHelpers();
        
            $data = $id->all();
            $objeto = DB::select('call getVidGaleria(
                                                "'.$data['cod_usuario'].'",
                                                "'.$data['cod_producto'].'"
                                                )');

                if (count($objeto) > 0) {
                return response()-> json(['vid'=>$objeto],200);
                
            }else{
                $message = 'no videos available';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getQuitarGaleriaVidPerfil(Request $id){
        try {
            $helper = new controladorHelpers();
        
            $data = $id->all();
            $objeto = DB::select('call getQuitarGaleriaVidPerfil(
                                                "'.$data['cod_vid'].'"
                                                )');

                if (count($objeto) > 0) {
                return response()-> json(['message'=>$objeto],200);
                
            }else{
                $message = 'no videos available';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getVisitasUsuarios(Request $id){
        try {
            $helper = new controladorHelpers();
            $ip = $_SERVER['REMOTE_ADDR'];
            $location = GeoIP::getLocation($ip);
            $data = $id->all();
            $objeto = DB::select('call getVisitasUsuarios(
                                                "'.$location->ip.'",
                                                "'.$location->country.'",
                                                "'.$location->state.'",
                                                "'.$location->city.'",
                                                "'.$location->postal_code.'",
                                                "'.$location->lat.'",
                                                "'.$location->lon.'",
                                                "'.$location->timezone.'"
                                                )');
                $message = 'Data cargada con exito';
                return response()-> json(['visitas'=>$objeto,'message'=>$message],200);
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getBloqueoVendedor(Request $id){
        try {
            $data = $id->all();
            $objeto = DB::select('call getBloqueoVendedor(
                                                    "'.$data['cod_usuario'].'",
                                                    "'.$data['cod_vendedor'].'"
                                                )');

                if (count($objeto) > 0) {
                return response()-> json(['message'=>$objeto],200);
                
            }else{
                $message = 'Failed process';
                return response()-> json(['message'=>$message],404);
            }
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getListaNegraUsuarioVendedor(Request $id){
        try {
            $data = $id->all();
            $objeto = DB::select('call getListaNegraUsuarioVendedor(
                                                    "'.$data['cod_usuario'].'"
                                                )');

            return response()-> json(['usuario'=>$objeto],200);
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getReportarVendedor(Request $id){
        try {
            $data = $id->all();
            $objeto = DB::select('call getReportarVendedor(
                                                    "'.$data['cod_usuario'].'",
                                                    "'.$data['cod_vendedor'].'",
                                                    "'.$data['comentario'].'"
                                                )');

                if (count($objeto) > 0) {
                return response()-> json(['resul'=>$objeto],200);
                
            }else{
                $message = 'Failed process';
                return response()-> json(['message'=>$message],404);
            }
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getUsuarioVendedorBloqueado(Request $id){
        try {
            $data = $id->all();
            $objeto = DB::select('call getUsuarioVendedorBloqueado()');

            return response()-> json(['usuario'=>$objeto],200);
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }

    public function updateUserPass(Request $request){
        $data = $request->all();
        $cod_usuario = $data['cod_usuario'];
        $newpass = $data['new_pass'];

        $user = sk_usuarios::where('cod_usuario',$cod_usuario)->first();
        $user->pass_usuario = $newpass;
        $user->pass_usuario_p = $newpass;
        $user->save();

        return response()-> json(['resul'=>'Ok'],200);

    }

    public function sendContactForm(Request $request){
        $data = $request->all();
        $name = $data['name'];
        $email = $data['email'];
        $subjectm = $data['subject'];
        $messages = $data['message'];

        $result = [
            'name' => $name,
            'email' => $email,
            'subjectm' => $subjectm,
            'message' => $messages
        ];

        $correo = new controladorContacto($name, $email, $subjectm, $messages);
        Mail::to('xmxavier@gmail.com')->send($correo);

        return response()->json([$result],200);

    }

    public function getNewSellers(){
        try {
            //$newSellers = sk_usuarios::with('images')->get();
/*             $newSellers = sk_usuarios::with('images')->whereHas('images', function($query){
                $query->where('estatus','A');
            })->get(); */

            $sql = 'SELECT sk_usuarios.cod_usuario, reg_perfil_usuario_img.cod_img, sk_usuarios.nom_usuario, sk_usuarios.correo, sk_usuarios.created FROM sk_usuarios
            INNER JOIN reg_perfil_usuario_img ON sk_usuarios.cod_usuario = reg_perfil_usuario_img.cod_usuario
            INNER JOIN inv_depositos_control ON sk_usuarios.cod_usuario = inv_depositos_control.cod_deposito
            WHERE reg_perfil_usuario_img.estatus = \'A\' AND inv_depositos_control.estatus = \'A\' AND sk_usuarios.tipo = \'DLR\'
            ORDER BY sk_usuarios.cod_usuario';

/*             $newSellers = DB::select('SELECT sk_usuarios.cod_usuario, reg_perfil_usuario_img.cod_img, sk_usuarios.nom_usuario, sk_usuarios.correo, sk_usuarios.created FROM sk_usuarios
            INNER JOIN reg_perfil_usuario_img ON sk_usuarios.cod_usuario = reg_perfil_usuario_img.cod_usuario
            INNER JOIN inv_depositos_control ON sk_usuarios.cod_usuario = inv_depositos_control.cod_deposito
            WHERE reg_perfil_usuario_img.estatus = \'A\' AND inv_depositos_control.estatus = \'A\' AND sk_usuarios.tipo = \'DLR\'
            ORDER BY sk_usuarios.cod_usuario'); */

            //$newSellers = DB::select('SELECT * FROM sk_usuarios
            $newSellers = DB::select('SELECT sk_usuarios.cod_usuario, sk_usuarios.nom_usuario, sk_usuarios.email, sk_usuarios.created, reg_perfil_usuario_img.cod_img FROM sk_usuarios
            INNER JOIN reg_perfil_usuario_img ON sk_usuarios.cod_usuario = reg_perfil_usuario_img.cod_usuario
            INNER JOIN inv_depositos_control ON sk_usuarios.cod_usuario = inv_depositos_control.cod_deposito
            WHERE reg_perfil_usuario_img.estatus = \'A\' AND inv_depositos_control.estatus = \'A\' AND sk_usuarios.tipo = \'DLR\'
            ORDER BY sk_usuarios.created desc');





            /* $newSellers = sk_usuarios::with('images')
            ->where('tipo','DLR')
            ->whereDate('created', '>=', now()->subDays(2))
            ->get(); */
            //$objeto_dep = DB::select('call getDepDlr()');


            if (count($newSellers) > 0) {
                $message = 'Deposits uploaded successfully';
                return response()-> json(['message'=> $message ,'dep'=>$newSellers,200]);
            }else{
                $message = 'There are no pending deposits to take';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (\Throwable $th) {
            return response()-> json(['message'=>$th, 'sql'=> $sql],500);
        }
    }

    public function getBuyers(){
        try {

            $buyers = sk_usuarios::with('images')
            ->where('tipo','MMBR')
            ->get();
            //$objeto_dep = DB::select('call getDepDlr()');
            if (count($buyers) > 0) {
                $message = 'Deposits uploaded successfully';
                return response()-> json(['message'=> $message ,'buyers'=>$buyers,200]);
            }else{
                $message = 'There are no pending deposits to take';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (\Throwable $th) {
            return response()-> json(['message'=>$th],500);
        }
    }
}

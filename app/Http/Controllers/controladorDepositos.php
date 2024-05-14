<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\usuario;
use App\Models\sweish_user;
use App\Models\sk_usuarios;
use App\Models\reg_perfil_usuario_img;
use App\Models\relacion_producto_img;
use App\Models\inv_depositos_control;
use App\Models\relacion_deposito_productos;
use App\Models\relacion_deposito_productos_operador;
use App\Models\reg_cod;
use App\Http\Controllers\controladorHelpers;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\controladorENV;
use DB;


class controladorDepositos extends Controller
{
    public function getDepositoProducto(Request $id){
        try {
            $data = $id->all();
            $objeto_dep = inv_depositos_control::where('cod_deposito',$data['cod_deposito'])
                                                ->where('cod_usuario',$data['cod_deposito'])->get();
            if (count($objeto_dep) > 0) {
                $objeto_prod = relacion_deposito_productos::where('cod_deposito',$data['cod_deposito'])->where('estatus','A')->get();
                $objeto_img = relacion_producto_img::where('cod_deposito',$data['cod_deposito'])->where('estatus','A')->get();
                $message = 'Products uploaded successfully';
                return response()-> json(['message'=> $message ,'img'=> $objeto_img,'deposito'=>$objeto_prod,'dlr'=>$objeto_dep,200]);
            }else{
                $message = 'Deposit '.$data['cod_deposito'].'  does not exist';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getDepositoProd(Request $id){
        try {
            $data = $id->all();
            $objeto_dep = inv_depositos_control::where('cod_deposito',$data['cod_deposito'])
                                                ->where('estatus','T')->where('cod_prdr',$data['cod_prdr'])->get();
            if (count($objeto_dep) > 0) {
                $objeto_prod = relacion_deposito_productos_operador::where('cod_deposito',$objeto_dep[0] ['cod_deposito'])->where('estatus','A')->get();
                $objeto_img = relacion_producto_img::where('cod_deposito',$objeto_dep[0] ['cod_deposito'])->where('estatus','A')->get();
                $message = 'Products uploaded successfully';
                return response()-> json(['message'=> $message ,'img'=> $objeto_img,'deposito'=>$objeto_prod,'dlr'=>$objeto_dep,200]);
            }else{
                $message = 'Deposit '.$data['cod_deposito'].'  does not exist';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getDepositoProdDlrBasic(Request $id){
        try {
            $data = $id->all();
            $objeto_dep = inv_depositos_control::where('cod_usuario',$data['cod_usuario'])->where('estatus','T')->get();
            if (count($objeto_dep) > 0) {
                $objeto_prod = relacion_deposito_productos::where('cod_deposito',$objeto_dep[0] ['cod_deposito'])->where('estatus','A')->get();
                $objeto_img = relacion_producto_img::where('cod_deposito',$objeto_dep[0] ['cod_deposito'])->where('estatus','A')->get();
                $message = 'Products uploaded successfully';
                return response()-> json(['message'=> $message ,'img'=> $objeto_img,'deposito'=>$objeto_prod,'dlr'=>$objeto_dep,200]);
            }else{
                $message = 'Deposit '.$data['cod_deposito'].'  does not exist';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getDepositoProdDlr(Request $id){
        try {
            $data = $id->all();
            $objeto_dep = inv_depositos_control::where('cod_deposito',$data['cod_usuario'])->get();
            if (count($objeto_dep) > 0) {

                $objeto_prod = DB::select('call getDepositoProdDlr(
                                                        "'.$data['cod_usuario'].'"
                                                        )');
                $message = 'Products uploaded successfully';
                return response()-> json(['message'=> $message ,'deposito'=>$objeto_prod,'dlr'=>$objeto_dep,200]);
            }else{
                $message = 'Seller '.$data['cod_usuario'].' does not exist';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getproductosSeller(Request $id){
        try {
            $helper = new controladorHelpers();
            $data = $id->all();
            $objeto_prod = DB::select('call getproductosSeller()');
            $deseo =  DB::select('call getDeseosActivos(
                                                 "'.$data['cod_usuario'].'"
                                                 )');

            if (count($objeto_prod) > 0) {
                foreach ($objeto_prod as $i) {
                    foreach ($deseo as $d) {
                        if ($i-> cod_producto === $d->cod_producto) {
                            $i -> des_deposito = $helper->DESEO;
                        }
                    }
                 }
                $message = 'Products uploaded successfully';
                return response()-> json(['message'=> $message ,'producto'=>$objeto_prod,200]);
            }else{
                $message = 'No products loaded';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getProd(Request $id){
        try {
            $helper = new controladorHelpers();
            $data = $id->all();
            $objeto_prod = DB::select('call getproductos()');
            $deseo =  DB::select('call getDeseosActivos(
                                                 "'.$data['cod_usuario'].'"
                                                 )');
            if (count($objeto_prod) > 0) {
                
                foreach ($objeto_prod as $i) {
                    foreach ($deseo as $d) {
                        if ($i-> cod_producto === $d->cod_producto) {
                            $i -> des_deposito = $helper->DESEO;
                        }
                    }
                 }
                $message = 'Products uploaded successfully';
                return response()-> json(['message'=> $message ,'producto'=>$objeto_prod,200]);
            }else{
                $message = 'No products loaded';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getDepDlr(Request $id){
        try {
            $data = $id->all();
            $objeto_dep = DB::select('call getDepDlr()');
            if (count($objeto_dep) > 0) {
                $message = 'Deposits uploaded successfully';
                return response()-> json(['message'=> $message ,'dep'=>$objeto_dep,200]);
            }else{
                $message = 'There are no pending deposits to take';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (\Throwable $th) {
            return response()-> json(['message'=>$th],404);
        }
    }
    public function getTomarDeposito(Request $id){
        try {
            $data = $id->all();
            $objeto = DB::select('call getTomarDeposito(
                                                        "'.$data['cod_prdr'].'",
                                                        "'.$data['cod_deposito'].'"
                                                        )');
            if (count($objeto) > 0) {

                foreach ($objeto as $value) {
                   DB::select('call getTomarDepositoProducto(
                                                "'.$value->cod_deposito.'",
                                                "'.$value->cod_producto.'",
                                                "'.$value->des_producto.'",
                                                "'.$value->cant_producto.'",
                                                "'.$value->cat_producto.'",
                                                "'.$value->um_producto.'",
                                                "'.$data['cod_prdr'].'",
                                                "'.$value->tipo_producto.'",
                                                "'.$value->estatus.'"
                                                )');
                }
                $message = 'Deposit taken successfully';
                return response()-> json(['message'=> $message ,200]);
            }else{
                $message = 'This warehouse has no products assigned yet';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }

    public function getProductsxSeller(Request $id){
        try {
            $data = $id->all();
            $objeto = relacion_deposito_productos::where('cod_deposito',$data['cod_usuario'])->get();

            if (count($objeto) > 0) {

                $message = 'Deposit taken successfully';
                return response()-> json(['message'=> $message ,200]);
            }else{
                $message = 'This warehouse has no products assigned yet';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }

    public function getDepDlrTomado(Request $id){
        try {
            $data = $id->all();
            $objeto_dep = DB::select('call getDepDlrTomado("'.$data['cod_prdr'].'")');
            if (count($objeto_dep) > 0) {
                $message = 'Deposits uploaded successfully';
                return response()-> json(['message'=> $message ,'dep'=>$objeto_dep,200]);
            }else{
                $message = 'You have no deposits in your list';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (\Throwable $th) {
            return response()-> json(['message'=>$th],404);
        }
    }
    public function getProductoOperador(Request $id){
        try {
            $helper = new controladorHelpers();
            $data = $id->all();
            $objeto_dep = DB::select('call getProductoOperador("'.$data['cod_deposito'].'")');
            if (count($objeto_dep) > 0) {
                $message = 'Products uploaded successfully';
                return response()-> json(['message'=> $message ,'producto'=>$objeto_dep,200]);
            }else{
                $message = 'You have no deposits in your list';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getProductoPrecioOperador(Request $id){
        try {
            $helper = new controladorHelpers();
            $data = $id->all();
            $objeto_dep = DB::select('call getProductoPrecioOperador(
                                                                        "'.$data['cod_producto'].'",
                                                                        "'.$data['cod_deposito'].'",
                                                                        "'.$data['des_producto'].'",
                                                                        "'.$data['cod_prdr'].'",
                                                                        "'.$data['um_producto_operador'].'",
                                                                        "'.$data['cat_producto'].'",
                                                                        "'.$data['tipo'].'",
                                                                        "'.$data['des_comentario'].'"
                                                                        )');
            if (count($objeto_dep) > 0) {
                /////SEGUIMIENTO DE OPERACION CREAR PRODUCTO/////
                DB::select('call getSegOperProd(
                    "'.$helper->ACTUALIZAR.'",
                    "",
                    "'.$data['cod_deposito'].'",
                    "'.$data['cod_producto'].'",
                    "",
                    "'.$data['des_producto'].'",
                    "'.$data['um_producto_operador'].'",
                    "'.$data['cat_producto'].'",
                    "'.$data['tipo'].'",
                    "'.$data['estatus'].'",
                    "'.$objeto_dep[0]->estatus.'"
                    )');
                $message = 'The price of this product was successfully updated';
                return response()-> json(['message'=> $message ,'resul'=>$objeto_dep ,200]);
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getProductoActivoHome(Request $id){
        try {
            $data = $id->all();
            $objeto_dep = DB::select('call getProductoActivoHome(
                                                                    "'.$data['cod_producto'].'",
                                                                    "'.$data['cod_deposito'].'"
                                                                    )');
            if (count($objeto_dep) > 0) {
                
                return response()-> json(['resul'=> $objeto_dep,200]);
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getProdLog(Request $id){
        try {
            $helper = new controladorHelpers();
            $data = $id->all();
            $objeto_prod =  DB::select('call getProdLog()');
                return response()-> json(['producto'=>$objeto_prod,200]);
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getRechazarProductoModerador(Request $id){
        try {
            $helper = new controladorHelpers();
            $ENV = new controladorENV();
            $data = $id->all();
            $objeto =  DB::select('call getRechazarProductoModerador(
                                                                            "'.$data['operacion'].'",
                                                                            "'.$data['cod_deposito'].'",
                                                                            "'.$data['cod_producto'].'",
                                                                            "'.$data['des_producto'].'",
                                                                            "'.$data['cod_img'].'",
                                                                            "'.$data['estatus_actual'].'",
                                                                            "'.$data['comentarios'].'"
                                                                            )');
                $objeto[0]->comentarios = $data['comentarios'].' Producto: '.$data['des_producto'];
                $ENV->getEnvCorreoSellerRechazoProducto($objeto);
                return response()-> json(['resul'=>'1',200]);
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getProdBuscar(Request $id){
        try {
            $helper = new controladorHelpers();
            $data = $id->all();
            $objeto_prod = DB::select('call getProdBuscar("'.$data['termino'].'")');
            $deseo =  DB::select('call getDeseosActivos(
                                                 "'.$data['cod_usuario'].'"
                                                 )');
            if (count($objeto_prod) > 0) {
                
                foreach ($objeto_prod as $i) {
                    foreach ($deseo as $d) {
                        if ($i-> cod_producto === $d->cod_producto) {
                            $i -> des_deposito = $helper->DESEO;
                        }
                    }
                 }
                $message = 'Products uploaded successfully';
                return response()-> json(['message'=> $message ,'producto'=>$objeto_prod,200]);
            }else{
                $message = 'No products loaded';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
}

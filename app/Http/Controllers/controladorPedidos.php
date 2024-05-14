<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\controladorHelpers;
use DB;

class controladorPedidos extends Controller
{
    public function getPedidoCarrito(Request $id){
        try {
            $helper = new controladorHelpers();
            $data = $id->all();
           /*  $objeto = sk_usuarios::where('cod_usuario',$data['cod_usuario'])->where('estatus','A')->get();
            $objeto_img = reg_perfil_usuario_img::where('cod_usuario',$data['cod_usuario'])->where('estatus','A')->get(); */

             $objeto_val = DB::select('call getEstatusProducto(
                                                        "'.$data['cod_producto'].'",
                                                        "'.$data['cod_deposito'].'"
                                                        )');
            if (count($objeto_val) > 0) {
                 $objeto = DB::select('call getPedidoCarrito(
                                                        "'.$data['cant_producto'].'",
                                                        "'.$data['cod_cliente'].'",
                                                        "'.$data['cod_deposito'].'",
                                                        "'.$data['cod_img'].'",
                                                        "'.$data['cod_producto'].'",
                                                        "'.$data['cod_vendedor'].'",
                                                        "'.$data['des_producto'].'",
                                                        "'.$data['precio_producto'].'",
                                                        "'.$data['um_producto'].'",
                                                        "'.$data['cat_producto'].'",
                                                        "'.$data['tipo'].'",
                                                        "'.$data['total_precio_pedido'].'",
                                                        "'.$data['total_precio_producto'].'"
                                                        )');
                if (count($objeto) > 0) {
                    if (intval($objeto[0]->xresul) === 1 ){
                        $message = 'This product already exists in the current order';
                        return response()-> json(['message'=>$message] ,404);
                    }
                    /* if ( && intval($objeto[0]->xresul) !== 1
                         && intval($objeto[0]->xresul) !== 4
                         && intval($objeto[0]->xresul) !== 3){
                        $pedido = DB::select('call getProductoPedido("'.$objeto[0]->xresul.'")');
                        $message = 'Product uploaded successfully';
                        return response()-> json(['pedido'=>$pedido ,'message'=>$message] ,200);
                    } */
                    if (intval($objeto[0]->xresul) !== 3 
                    && intval($objeto[0]->xresul) !== 1
                    && intval($objeto[0]->xresul) !== 2){
                        $pedido = DB::select('call getProductoPedido("'.$objeto[0]->xresul.'")');
                         /////SEGUIMIENTO DE OPERACION CREAR PRODUCTO/////
                    DB::select('call getSegOperProd(
                        "'.$helper->CARRITO.'",
                        "'.$objeto[0]->xresul.'",
                        "'.$data['cod_deposito'].'",
                        "'.$data['cod_producto'].'",
                        "",
                        "'.$data['des_producto'].'",
                        "'.$data['precio_producto'].'",
                        "'.$data['cat_producto'].'",
                        "'.$data['tipo'].'",
                        "'.$data['estatus'].'",
                        "T"
                        )');
                        $message = 'Product added to cart successfully';
                    return response()-> json(['pedido'=>$pedido ,'message'=>$message] ,200);
                    }
                    if (intval($objeto[0]->xresul) === 3){
                        $message = 'This order can only have a maximum of 5 products';
                        return response()-> json(['message'=>$message] ,404);
                    }
                }else{
                    $message = 'Query error';
                    return response()-> json(['message'=>$objeto],404);
                }
            }else{
                $message = 'This product has changed its status, you cannot use it again';
                return response()-> json(['message'=>$message] ,404);
            }
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getPedidoCarritoActivo(Request $id){
        try {
            $data = $id->all();

            $objeto = DB::select('call getPedidoCarritoActivo("'.$data['cod_usuario'].'")');
                
            if (count($objeto) > 0) {
                return response()-> json(['pedido'=>$objeto] ,200);
            }else{
                $message = 'This order have not products';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getPedidoCliente(Request $id){
        try {
            $data = $id->all();

            $objeto = DB::select('call getPedidoCliente("'.$data['cod_usuario'].'")');
                
            if (count($objeto) > 0) {
                return response()-> json(['pedido'=>$objeto] ,200);
            }else{
                $message = 'You have no active orders';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getPedidoClienteOperador(Request $id){
        try {
            $data = $id->all();

            $objeto = DB::select('call getPedidoClienteOperador("'.$data['cod_usuario'].'")');
                
            if (count($objeto) > 0) {
                return response()-> json(['pedido'=>$objeto] ,200);
            }else{
                $message = 'You have no active orders';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getTdcUsuarioActiva(Request $id){
        try {
            $data = $id->all();

            $objeto = DB::select('call getTdcUsuarioActiva("'.$data['cod_usuario'].'")');
                
            if (count($objeto) > 0) {
                return response()-> json(['tdc'=>$objeto] ,200);
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$objeto],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getFacturarPedido(Request $id){
        try {
            $helper = new controladorHelpers();
            $data = $id->all();
            /* $producto = $data['producto'];
            foreach ($producto as $i => $val) {
                DB::select('call getFacturarPedido("'.$val['pedido'][0]['cod_pedido_carrito'].'")');
            } */

            $objeto = DB::select('call getFacturarPedido(
                                                            "'.$data['cod_pedido_carrito'].'",
                                                            "'.$data['cod_stripe'].'")');
                
            if (count($objeto) > 0) {
                if (intval($objeto[0]->resul) > 0) {
                    
                    $objeto_producto = DB::select('call getProductoPedido(
                                                    "'.$data['cod_pedido_carrito'].'")');
                    foreach ($objeto_producto as  $value) {
                        DB::select('call getFacturarProducto(
                                            "'.$value->cod_pedido_carrito.'",
                                            "'.$value->cod_producto.'"
                                            )');
                        /////SEGUIMIENTO DE OPERACION FACTURAR PRODUCTO/////
                                        DB::select('call getSegOperProd(
                                            "'.$helper->FACTURACION.'",
                                            "'.$data['cod_pedido_carrito'].'",
                                            "'.$value->cod_deposito.'",
                                            "'.$value->cod_producto.'",
                                            "",
                                            "'.$value->des_producto.'",
                                            "'.$value->precio_producto.'",
                                            "'.$value->cat_producto.'",
                                            "'.$value->tipo_producto_cat.'",
                                            "T",
                                            "P"
                                            )');
                    }
                    $message = 'Order invoiced successfully';
                return response()-> json(['resul'=>$objeto,'message'=>$message] ,200);
                }
                if (intval($objeto[0]->resul) <= 0) {
                    $message = 'Order not found';
                return response()-> json(['message'=>$message] ,404);
                }
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getProductoPedido(Request $id){
        try {
            $data = $id->all();
         
            $objeto = DB::select('call getProductoPedido("'.$data['cod_pedido_carrito'].'")');
                
            if (count($objeto) > 0) {
                return response()-> json(['resul'=>$objeto] ,200);
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getQuitarElementosPedido(Request $id){
        try {
            $helper = new controladorHelpers();
            $data = $id->all();
         
            $objeto = DB::select('call getQuitarElementosPedido(
                                                        "'.$data['cod_pedido_carrito'].'",
                                                        "'.$data['cod_deposito'].'",
                                                        "'.$data['cod_producto'].'",
                                                        "'.$data['total_precio_pedido'].'",
                                                        "'.$data['total_precio_producto'].'"
                                                        )');
                
            if (count($objeto) > 0) {
                    $pedido = DB::select('call getPedidoConsulta("'.$data['cod_pedido_carrito'].'")');
                         /////SEGUIMIENTO DE OPERACION CREAR PRODUCTO/////
                DB::select('call getSegOperProd(
                    "'.$helper->DEVOLUCION.'",
                    "'.$data['cod_pedido_carrito'].'",
                    "'.$data['cod_deposito'].'",
                    "'.$data['cod_producto'].'",
                    "",
                    "'.$data['des_producto'].'",
                    "'.$data['precio_producto'].'",
                    "'.$data['cat_producto'].'",
                    "'.$data['tipo_producto_cat'].'",
                    "T",
                    "A"
                    )');
                    $message = 'Order successfully updated';
                return response()-> json(['pedido'=>$pedido,'message'=>$message],200);
                
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getOfertaProducto(Request $id){
        try {
            $data = $id->all();
         
            $objeto = DB::select('call getOfertaProducto(
                                                        "'.$data['cod_oferta'].'",
                                                        "'.$data['cod_usuario'].'",
                                                        "'.$data['cod_prdr'].'",
                                                        "'.$data['cod_producto'].'",
                                                        "'.$data['des_producto'].'",
                                                        "'.$data['cod_deposito'].'",
                                                        "'.$data['cod_img'].'",
                                                        "'.$data['cant_producto'].'",
                                                        "'.$data['cat_producto'].'",
                                                        "'.$data['tipo_producto_cat'].'",
                                                        "'.$data['um_producto_oferta'].'",
                                                        "'.$data['um_producto_operador'].'"
                                                        )');
                
            if (count($objeto) > 0) {
                    $message = 'The offer was made successfully';
                return response()-> json(['message'=>$message],200);
                
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getProcessarOferta(Request $id){
        try {
            $helper = new controladorHelpers();
            $data = $id->all();
         
            $objeto = DB::select('call getProcessarOferta(
                                                        "'.$data['cod_oferta'].'"
                                                        )');
                
            if (count($objeto) > 0) {

                $objeto_pedido = DB::select('call getPedidoCarrito(
                                                "'.$objeto[0]-> cant_producto.'",
                                                "'.$objeto[0]-> cod_usuario.'",
                                                "'.$objeto[0]-> cod_deposito.'",
                                                "'.$objeto[0]-> cod_img.'",
                                                "'.$objeto[0]-> cod_producto.'",
                                                "'.$objeto[0]-> cod_prdr.'",
                                                "'.$objeto[0]-> des_producto.'",
                                                "'.$objeto[0]-> um_producto_oferta.'",
                                                "'.$objeto[0]-> um_producto_oferta.'",
                                                "'.$objeto[0]-> cat_producto.'",
                                                "'.$objeto[0]-> tipo_producto_cat.'",
                                                "'.$objeto[0]-> um_producto_oferta.'",
                                                "'.$objeto[0]-> um_producto_oferta.'"
                                                )');
                DB::select('call getProductoPrecioOperador(
                                                            "'.$objeto[0]-> cod_producto.'",
                                                            "'.$objeto[0]-> cod_deposito.'",
                                                            "'.$objeto[0]-> des_producto.'",
                                                            "'.$objeto[0]-> cod_prdr.'",
                                                            "'.$objeto[0]-> um_producto_oferta.'",
                                                            "'.$objeto[0]-> cat_producto.'",
                                                            "'.$objeto[0]-> tipo_producto_cat.'",
                                                            "Actualizado por oferta"
                                                                        )');

                    if (count($objeto_pedido) > 0) {
                if (intval($objeto_pedido[0]->xresul) === 1 ){
                    $message = 'This product already exists in the current order';
                    return response()-> json(['message'=>$message] ,404);
                }
                /* if ( && intval($objeto_pedido[0]->xresul) !== 1
                     && intval($objeto_pedido[0]->xresul) !== 4
                     && intval($objeto_pedido[0]->xresul) !== 3){
                    $pedido = DB::select('call getProductoPedido("'.$objeto_pedido[0]->xresul.'")');
                    $message = 'Product uploaded successfully';
                    return response()-> json(['pedido'=>$pedido ,'message'=>$message] ,200);
                } */
                if (intval($objeto_pedido[0]->xresul) !== 3 
                && intval($objeto_pedido[0]->xresul) !== 1
                && intval($objeto_pedido[0]->xresul) !== 2){
                    $pedido = DB::select('call getProductoPedido("'.$objeto_pedido[0]->xresul.'")');
                         /////SEGUIMIENTO DE OPERACION CREAR PRODUCTO/////
                DB::select('call getSegOperProd(
                    "'.$helper->OFERTA.'",
                    "'.$objeto_pedido[0]->xresul.'",
                    "'.$objeto[0]-> cod_deposito.'",
                    "'.$objeto[0]-> cod_producto.'",
                    "",
                    "'.$objeto[0]-> des_producto.'",
                    "'.$objeto[0]-> um_producto_oferta.'",
                    "'.$objeto[0]-> cat_producto.'",
                    "'.$objeto[0]-> tipo_producto_cat.'",
                    "A",
                    "T"
                    )');
                    $message = 'Process successfully';
                return response()-> json(['pedido'=>$pedido ,'message'=>$message] ,200);
                }
                if (intval($objeto_pedido[0]->xresul) === 3){
                    $message = 'This order can only have a maximum of 5 products';
                    return response()-> json(['message'=>$message] ,404);
                }
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$objeto_pedido],404);
            }
                
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$message],404);
            }
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getOrdenesProducto(Request $id){
        try {
            $data = $id->all();
         
            $objeto = DB::select('call getOrdenesProducto(
                                                        "'.$data['cod_deposito'].'"
                                                        )');
                
            if (count($objeto) > 0) {
                    $message = 'Orders loaded successfull';
                return response()-> json(['message'=>$message, 'ordenes' =>$objeto],200);
                
            }else{
                $message = 'There are no pending orders';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getCheckoutProducto(Request $id){
        try {
            $helper = new controladorHelpers();
            $um_disponible = 0;
            $um_espera = 0;
            $um_bloqueado = 0;
            $um_total = 0;
            $data = $id->all();
         
            $objeto = DB::select('call getCheckoutProducto(
                                                        "'.$data['cod_producto'].'",
                                                        "'.$data['cod_deposito'].'",
                                                        "'.$data['cod_pedido_carrito'].'"
                                                        )');
                
            if (count($objeto) > 0) {
              
                 /////REGISTRO SECUENCIA COMPRA/////
                 DB::select('call getSecuenciaEntrega(
                    "'.$data['cod_producto'].'",
                    "'.$data['cod_prdr'].'",
                    "'.$data['cod_deposito'].'",
                    "'.$data['cod_pedido_carrito'].'",
                    "'.$data['des_producto'].'",
                    "'.$data['precio_producto'].'",
                    "'.$data['um_producto'].'",
                    "'.$data['cat_producto'].'",
                    "'.$data['tipo_producto_cat'].'",
                    "'.$helper->PENDIENTE.'",
                    "'.$helper->FACTURACION.'",
                    "'.$helper->PENDIENTE.'",
                    "",
                    "",
                    ""
                    )');
                /////SEGUIMIENTO DE OPERACION CREAR PRODUCTO/////
                DB::select('call getSegOperProd(
                    "'.$helper->SALIDA.'",
                    "'.$data['cod_pedido_carrito'].'",
                    "'.$data['cod_deposito'].'",
                    "'.$data['cod_producto'].'",
                    "",
                    "'.$data['des_producto'].'",
                    "'.$data['um_producto'].'",
                    "'.$data['cat_producto'].'",
                    "'.$data['tipo_producto_cat'].'",
                    "'.$data['estatus'].'",
                    "V"
                    )');
                //////BILLETERA/////////////////////
                $objeto_vendor = DB::select('call getBilleteraVendedor(
                                                        "'.$data['cod_deposito'].'"
                                                        )');
               
            if (count($objeto_vendor) > 0) {
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
                                                                "'.$data['cod_deposito'].'",
                                                                "'.$data['cod_deposito'].'",
                                                                "'.$objeto_vendor[0]->um_disponible.'",
                                                                "'.$um_disponible.'",
                                                                "'.$objeto_vendor[0]->um_espera.'",
                                                                "'.$um_espera.'",
                                                                "'.$objeto_vendor[0]->um_bloqueado.'",
                                                                "'.$um_bloqueado.'",
                                                                "'.$objeto_vendor[0]->um_total.'",
                                                                "'.$um_total.'"
                                                                )');

               }
            }else{ 
                $objeto_billetera = DB::select('call getCrearBilleteraVendedor(
                                                                "'.$data['cod_deposito'].'",
                                                                "'.$data['cod_deposito'].'"
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
                                                                "'.$data['cod_deposito'].'",
                                                                "'.$data['cod_deposito'].'",
                                                                "'.$objeto_billetera[0]->um_disponible.'",
                                                                "'.$um_disponible.'",
                                                                "'.$objeto_billetera[0]->um_espera.'",
                                                                "'.$um_espera.'",
                                                                "'.$objeto_billetera[0]->um_bloqueado.'",
                                                                "'.$um_bloqueado.'",
                                                                "'.$objeto_billetera[0]->um_total.'",
                                                                "'.$um_total.'"
                                                                )');

                }
                  }
                    $message = 'Product checkout successfull';
                return response()-> json(['message'=>$message],200);
                
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$message],404);
            }
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getDelivery(Request $id){
        try {
            $helper = new controladorHelpers();
            $data = $id->all();
         
            $objeto = DB::select('call getDelivery(
                                                    "'.$data['cod_deposito'].'"
                                                        )');
                
            if (count($objeto) > 0) {
                    $message = 'Product delivery loaded successfull';
                return response()-> json(['message'=>$message,'delivery'=> $objeto],200);
                
            }else{
                $message = 'It has no pending deliveries';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getDeliveryOperador(Request $id){
        try {
            $helper = new controladorHelpers();
            $data = $id->all();
         
            $objeto = DB::select('call getDeliveryOperador(
                                                    "'.$data['cod_prdr'].'"
                                                        )');
                
            if (count($objeto) > 0) {
                    $message = 'Product delivery loaded successfull';
                return response()-> json(['message'=>$message,'delivery'=> $objeto],200);
                
            }else{
                $message = 'It has no pending deliveries';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getdeliveryProducto(Request $id){
        try {
            $helper = new controladorHelpers();
            $um_disponible = 0;
            $um_espera = 0;
            $um_bloqueado = 0;
            $um_total = 0;
            $data = $id->all();
         
            $objeto = DB::select('call getdeliveryProducto(
                                                        "'.$data['cod_producto'].'",
                                                        "'.$data['cod_deposito'].'",
                                                        "'.$data['cod_pedido_carrito'].'"
                                                        )');
                
            if (count($objeto) > 0) {
                  /////REGISTRO SECUENCIA COMPRA/////
                  DB::select('call getCambioSecuenciaCompra(
                    "'.$data['cod_producto'].'",
                    "'.$data['cod_deposito'].'",
                    "'.$data['cod_pedido_carrito'].'",
                    "'.$data['des_producto'].'",
                    "'.$helper->DELIVERY.'",
                    "'.$helper->ENVIADO.'",
                    "",
                    "'.$helper->DELIVERY.'",
                    "",
                    ""
                    )');
                /////SEGUIMIENTO DE OPERACION CREAR PRODUCTO/////
                DB::select('call getSegOperProd(
                    "'.$helper->ENVIADO.'",
                    "'.$data['cod_pedido_carrito'].'",
                    "'.$data['cod_deposito'].'",
                    "'.$data['cod_producto'].'",
                    "",
                    "'.$data['des_producto'].'",
                    "'.$data['um_producto'].'",
                    "'.$data['cat_producto'].'",
                    "'.$data['tipo_producto_cat'].'",
                    "'.$data['estatus'].'",
                    "'.$helper->DELIVERY.'"
                    )');

                    //////BILLETERA/////////////////////
                $objeto_vendor = DB::select('call getBilleteraVendedor(
                                                        "'.$data['cod_deposito'].'"
                                                        )');
               
            if (count($objeto_vendor) > 0) {
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
                                                                "'.$data['cod_deposito'].'",
                                                                "'.$data['cod_deposito'].'",
                                                                "'.$objeto_vendor[0]->um_disponible.'",
                                                                "'.$um_disponible.'",
                                                                "'.$objeto_vendor[0]->um_espera.'",
                                                                "'.$um_espera.'",
                                                                "'.$objeto_vendor[0]->um_bloqueado.'",
                                                                "'.$um_bloqueado.'",
                                                                "'.$objeto_vendor[0]->um_total.'",
                                                                "'.$um_total.'"
                                                                )');

               }
            }else{ 
                $objeto_billetera = DB::select('call getCrearBilleteraVendedor(
                                                                "'.$data['cod_deposito'].'",
                                                                "'.$data['cod_deposito'].'"
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
                                                                "'.$data['cod_deposito'].'",
                                                                "'.$data['cod_deposito'].'",
                                                                "'.$objeto_billetera[0]->um_disponible.'",
                                                                "'.$um_disponible.'",
                                                                "'.$objeto_billetera[0]->um_espera.'",
                                                                "'.$um_espera.'",
                                                                "'.$objeto_billetera[0]->um_bloqueado.'",
                                                                "'.$um_bloqueado.'",
                                                                "'.$objeto_billetera[0]->um_total.'",
                                                                "'.$um_total.'"
                                                                )');

                }
                  }
                    $message = 'Product delivery successfull';
                return response()-> json(['message'=>$message],200);
                
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getReceivedProducto(Request $id){
        try {
            $helper = new controladorHelpers();
            $um_disponible = 0;
            $um_espera = 0;
            $um_bloqueado = 0;
            $um_total = 0;
            $data = $id->all();
         
            $objeto = DB::select('call getReceivedProducto(
                                                        "'.$data['cod_producto'].'",
                                                        "'.$data['cod_deposito'].'",
                                                        "'.$data['cod_pedido_carrito'].'"
                                                        )');
                
            if (count($objeto) > 0) {
                  /////REGISTRO SECUENCIA COMPRA/////
                  DB::select('call getCambioSecuenciaCompra(
                    "'.$data['cod_producto'].'",
                    "'.$data['cod_deposito'].'",
                    "'.$data['cod_pedido_carrito'].'",
                    "'.$data['des_producto'].'",
                    "'.$helper->RECIBIDO.'",
                    "'.$helper->ENTREGADO.'",
                    "",
                    "",
                    "'.$helper->RECIBIDO.'",
                    ""
                    )');
                    DB::select('call getCambioSecuenciaCompra(
                        "'.$data['cod_producto'].'",
                        "'.$data['cod_deposito'].'",
                        "'.$data['cod_pedido_carrito'].'",
                        "'.$data['des_producto'].'",
                        "'.$helper->COMPLETO.'",
                        "'.$helper->FINALIZADO.'",
                        "",
                        "",
                        "",
                        "'.$helper->COMPLETO.'"
                        )');
                /////SEGUIMIENTO DE OPERACION CREAR PRODUCTO/////
                DB::select('call getSegOperProd(
                    "'.$helper->ENTREGADO.'",
                    "'.$data['cod_pedido_carrito'].'",
                    "'.$data['cod_deposito'].'",
                    "'.$data['cod_producto'].'",
                    "",
                    "'.$data['des_producto'].'",
                    "'.$data['um_producto'].'",
                    "'.$data['cat_producto'].'",
                    "'.$data['tipo_producto_cat'].'",
                    "'.$helper->DELIVERY.'",
                    "'.$helper->RECIBIDO.'"
                    )');

                DB::select('call getSegOperProd(
                    "'.$helper->FINALIZADO.'",
                    "'.$data['cod_pedido_carrito'].'",
                    "'.$data['cod_deposito'].'",
                    "'.$data['cod_producto'].'",
                    "",
                    "'.$data['des_producto'].'",
                    "'.$data['um_producto'].'",
                    "'.$data['cat_producto'].'",
                    "'.$data['tipo_producto_cat'].'",
                    "'.$helper->RECIBIDO.'",
                    "'.$helper->COMPLETO.'"
                    )');

                     //////BILLETERA/////////////////////
                $objeto_vendor = DB::select('call getBilleteraVendedor(
                                                        "'.$data['cod_deposito'].'"
                                                        )');
               
            if (count($objeto_vendor) > 0) {
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
                                                                "'.$data['cod_deposito'].'",
                                                                "'.$data['cod_deposito'].'",
                                                                "'.$objeto_vendor[0]->um_disponible.'",
                                                                "'.$um_disponible.'",
                                                                "'.$objeto_vendor[0]->um_espera.'",
                                                                "'.$um_espera.'",
                                                                "'.$objeto_vendor[0]->um_bloqueado.'",
                                                                "'.$um_bloqueado.'",
                                                                "'.$objeto_vendor[0]->um_total.'",
                                                                "'.$um_total.'"
                                                                )');

               }
            }else{ 
                $objeto_billetera = DB::select('call getCrearBilleteraVendedor(
                                                                "'.$data['cod_deposito'].'",
                                                                "'.$data['cod_deposito'].'"
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
                                                                "'.$data['cod_deposito'].'",
                                                                "'.$data['cod_deposito'].'",
                                                                "'.$objeto_billetera[0]->um_disponible.'",
                                                                "'.$um_disponible.'",
                                                                "'.$objeto_billetera[0]->um_espera.'",
                                                                "'.$um_espera.'",
                                                                "'.$objeto_billetera[0]->um_bloqueado.'",
                                                                "'.$um_bloqueado.'",
                                                                "'.$objeto_billetera[0]->um_total.'",
                                                                "'.$um_total.'"
                                                                )');

                }
                  }
                    $message = 'Product delivery successfull';
                return response()-> json(['message'=>$message],200);
                
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getDeliveryCliente(Request $id){
        try {
            $helper = new controladorHelpers();
            $data = $id->all();
            $arry = [];
            $objeto = DB::select('call getDeliveryCliente(
                                                    "'.$data['cod_usuario'].'"
                                                        )');
            if (count($objeto) > 0) {
                foreach ($objeto as $value) {
                    $xvalor = DB::select('call getProductoSecuenciaCompra(
                                                                    "'.$value->cod_pedido_carrito.'"
                                                                        )');
                    if (count($xvalor) > 0) {
                        array_push($arry,$xvalor[0]);
                    }
                }
                    $message = 'Product delivery loaded successfull';
                return response()-> json(['message'=>$message,'delivery'=> $arry],200);
            }else{
                $message = 'It has no pending deliveries';
                return response()-> json(['message'=>$message],404);
            }
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getProductosOrdenActiva(Request $id){
        try {
            $helper = new controladorHelpers();
            $data = $id->all();
            $objeto = DB::select('call getProductosOrdenActiva(
                                                    "'.$data['cod_usuario'].'"
                                                        )');
            if (count($objeto) > 0) {
                    $message = 'Product  loaded successfull';
                return response()-> json(['message'=>$message,'producto'=> $objeto],200);
                
            }else{
                $message = 'here are no active orders';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }

    public function getDeseosUsuario(Request $id){
        try {
            $helper = new controladorHelpers();
            $data = $id->all();
            $objeto = DB::select('call getDeseosUsuario(
                                                        "'.$data['cod_cliente'].'",
                                                        "'.$data['cod_deposito'].'",
                                                        "'.$data['cod_img'].'",
                                                        "'.$data['cod_producto'].'",
                                                        "'.$data['cod_vendedor'].'",
                                                        "'.$data['des_producto'].'",
                                                        "'.$data['precio_producto'].'",
                                                        "'.$data['um_producto'].'",
                                                        "'.$data['cat_producto'].'",
                                                        "'.$data['tipo'].'"
                                                        )');
               
            if (count($objeto) > 0) {
                if (intval($objeto[0]->xresul) === 1 ){
                    $message = 'This product already exists in YOUR WISHES';
                    return response()-> json(['message'=>$message] ,404);
                }
              
                if (intval($objeto[0]->xresul) === 0){
                    
                    $message = 'Process successfully';
                return response()-> json(['message'=>$message] ,200);
                }
              
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$objeto],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getQuitarDeseo(Request $id){
        try {
            $helper = new controladorHelpers();
            $data = $id->all();
         
            $objeto = DB::select('call getQuitarDeseo(
                                                        "'.$data['cod_deposito'].'",
                                                        "'.$data['cod_usuario'].'",
                                                        "'.$data['cod_producto'].'"
                                                        )');
                
            if (count($objeto) > 0) {
                    $message = 'Successfully forgotten wish';
                return response()-> json(['message'=>$message],200);
                
            }else{
                $message = 'Query error';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
    public function getDeseosActivos(Request $id){
        try {
            $data = $id->all();

            $objeto = DB::select('call getDeseosActivos(
                                                        "'.$data['cod_usuario'].'")');
                
            if (count($objeto) > 0) {
                return response()-> json(['deseos'=>$objeto] ,200);
            }else{
                $message = 'You have no wishes on your list';
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (Exception $e) {
            return response()-> json(['message'=>$e],404);
        }
    }
}

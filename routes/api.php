<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('test', function () {
    $users = User::get();
    return $users;
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::group(['prefix' => 'auth'], function () {

    Route::post('login', 'App\Http\Controllers\API\AuthController@login');
    Route::post('signup', 'App\Http\Controllers\API\AuthController@signUp');
    Route::post('updatepass', 'App\Http\Controllers\API\AuthController@updatepass');
    Route::post('getVisitasUsuarios','App\Http\Controllers\controladorUsuarios@getVisitasUsuarios');
    Route::post('usuario_miembro','App\Http\Controllers\controladorUsuarios@getCrearMiembros');
    Route::post('getDefinicionSitema','App\Http\Controllers\controladorDefiniciones@getDefinicionSitema');
    Route::post('getCorreoValido','App\Http\Controllers\controladorAccesos@getCorreoValido');
    Route::post('getCodEnvPass','App\Http\Controllers\controladorAccesos@getCodEnvPass');
    Route::post('getCodEnvPassUpdate','App\Http\Controllers\controladorAccesos@getCodEnvPassUpdate');




    Route::get('testapi', function(){
        return "Api public respnose";
    });
  
    Route::group(['middleware' => 'auth:api'], function() {

        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'App\Http\Controllers\API\AuthController@user');

        Route::post('getPerfilAccesoMiembros','App\Http\Controllers\controladorAccesos@getPerfilAccesoMiembros');

        
        Route::post('usuario','App\Http\Controllers\controladorUsuarios@getUsuario');
        Route::post('getImgPerfil','App\Http\Controllers\controladorUsuarios@getImgPerfil');
        Route::post('getImgDLR','App\Http\Controllers\controladorUsuarios@getImgDLR');
        Route::post('getUsuarioSwiper','App\Http\Controllers\controladorUsuarios@getUsuarioSwiper');
        Route::post('getImgProd','App\Http\Controllers\controladorUsuarios@getImgProd');
        Route::post('getBilleteraVendedor','App\Http\Controllers\controladorUsuarios@getBilleteraVendedor');

        Route::post('getImgProducto','App\Http\Controllers\controladorUsuarios@getImgProducto');
        Route::post('getDataUsuario','App\Http\Controllers\controladorUsuarios@getDataUsuario');



        Route::post('getUsuarioSwiperSeller','App\Http\Controllers\controladorUsuarios@getUsuarioSwiperSeller');

Route::post('getPrdrIdcloud','App\Http\Controllers\controladorUsuarios@getPrdrIdcloud');
Route::post('getLimitesUpdate','App\Http\Controllers\controladorUsuarios@getLimitesUpdate');
Route::post('getListaNegraUsuarioVendedor','App\Http\Controllers\controladorUsuarios@getListaNegraUsuarioVendedor');

Route::post('getUpdateIdcloud','App\Http\Controllers\controladorUsuarios@getUpdateIdcloud');

Route::post('getQuitarFavoritos','App\Http\Controllers\controladorUsuarios@getQuitarFavoritos');

Route::post('getUsuarioParametro','App\Http\Controllers\controladorUsuarios@getUsuarioParametro');
Route::post('getUsuarioParametroIdCloud','App\Http\Controllers\controladorUsuarios@getUsuarioParametroIdCloud');
Route::post('getUsuarioChat','App\Http\Controllers\controladorUsuarios@getUsuarioChat');
Route::post('getUpdateUsuario','App\Http\Controllers\controladorUsuarios@getUpdateUsuario');
Route::post('getImgProductoVidGaleria','App\Http\Controllers\controladorUsuarios@getImgProductoVidGaleria');
Route::post('getVidGaleria','App\Http\Controllers\controladorUsuarios@getVidGaleria');
Route::post('getVidGaleriaPerfil','App\Http\Controllers\controladorUsuarios@getVidGaleriaPerfil');
Route::post('getImgProductoVidGaleriaPerfil','App\Http\Controllers\controladorUsuarios@getImgProductoVidGaleriaPerfil');
Route::post('getQuitarGaleriaVidPerfil','App\Http\Controllers\controladorUsuarios@getQuitarGaleriaVidPerfil');

Route::post('getBloqueoVendedor','App\Http\Controllers\controladorUsuarios@getBloqueoVendedor');
Route::post('getReportarVendedor','App\Http\Controllers\controladorUsuarios@getReportarVendedor');
Route::post('getUsuarioVendedorBloqueado','App\Http\Controllers\controladorUsuarios@getUsuarioVendedorBloqueado');

Route::post('menu_app_','App\Http\Controllers\controladorMenu@getMenu');
Route::post('perfiles','App\Http\Controllers\controladorAccesos@getPerfilAcceso');
Route::post('getCodEnv','App\Http\Controllers\controladorAccesos@getCodEnv');

Route::post('getPerfilAccesoVisitante','App\Http\Controllers\controladorAccesos@getPerfilAccesoVisitante');

Route::post('getDepositoProd','App\Http\Controllers\controladorDepositos@getDepositoProd');

Route::post('getproductosSeller','App\Http\Controllers\controladorDepositos@getproductosSeller');

Route::post('getDepositoProducto','App\Http\Controllers\controladorDepositos@getDepositoProducto');
Route::post('getProd','App\Http\Controllers\controladorDepositos@getProd');
Route::post('getProdBuscar','App\Http\Controllers\controladorDepositos@getProdBuscar');
Route::post('getDepositoProdDlr','App\Http\Controllers\controladorDepositos@getDepositoProdDlr');
Route::post('getDepositoProdDlrBasic','App\Http\Controllers\controladorDepositos@getDepositoProdDlrBasic');
Route::post('getProdLog','App\Http\Controllers\controladorDepositos@getProdLog');
Route::post('getRechazarProductoModerador','App\Http\Controllers\controladorDepositos@getRechazarProductoModerador');

Route::post('getDepDlr','App\Http\Controllers\controladorDepositos@getDepDlr');
Route::post('getTomarDeposito','App\Http\Controllers\controladorDepositos@getTomarDeposito');
Route::post('getProductsxSeller','App\Http\Controllers\controladorDepositos@getProductsxSeller');
Route::post('getDepDlrTomado','App\Http\Controllers\controladorDepositos@getDepDlrTomado');
Route::post('getProductoOperador','App\Http\Controllers\controladorDepositos@getProductoOperador');
Route::post('getProductoPrecioOperador','App\Http\Controllers\controladorDepositos@getProductoPrecioOperador');

Route::post('getProductoActivoHome','App\Http\Controllers\controladorDepositos@getProductoActivoHome');

Route::post('getEventosLikeDislike','App\Http\Controllers\controladorEventos@getEventosLikeDislike');
Route::post('getFavoritos','App\Http\Controllers\controladorEventos@getFavoritos');
Route::post('getQuitarDislike','App\Http\Controllers\controladorEventos@getQuitarDislike');

Route::post('getprod','App\Http\Controllers\controladorUsuarios@getprod');
Route::post('getNewSellers','App\Http\Controllers\controladorUsuarios@getNewSellers');
Route::post('getBuyers','App\Http\Controllers\controladorUsuarios@getBuyers');
Route::post('getPedidoCarrito','App\Http\Controllers\controladorPedidos@getPedidoCarrito');
Route::post('getPedidoCarritoActivo','App\Http\Controllers\controladorPedidos@getPedidoCarritoActivo');
Route::post('getPedidoCliente','App\Http\Controllers\controladorPedidos@getPedidoCliente');
Route::post('getTdcUsuarioActiva','App\Http\Controllers\controladorPedidos@getTdcUsuarioActiva');
Route::post('getFacturarPedido','App\Http\Controllers\controladorPedidos@getFacturarPedido');
Route::post('getProductoPedido','App\Http\Controllers\controladorPedidos@getProductoPedido');
Route::post('getQuitarElementosPedido','App\Http\Controllers\controladorPedidos@getQuitarElementosPedido');
Route::post('getOfertaProducto','App\Http\Controllers\controladorPedidos@getOfertaProducto');
Route::post('getProcessarOferta','App\Http\Controllers\controladorPedidos@getProcessarOferta');
Route::post('getdeliveryProducto','App\Http\Controllers\controladorPedidos@getdeliveryProducto');
Route::post('getDelivery','App\Http\Controllers\controladorPedidos@getDelivery');
Route::post('getDeliveryOperador','App\Http\Controllers\controladorPedidos@getDeliveryOperador');
Route::post('getReceivedProducto','App\Http\Controllers\controladorPedidos@getReceivedProducto');
Route::post('getDeliveryCliente','App\Http\Controllers\controladorPedidos@getDeliveryCliente');
Route::post('getProductosOrdenActiva','App\Http\Controllers\controladorPedidos@getProductosOrdenActiva');

Route::post('getDeseosUsuario','App\Http\Controllers\controladorPedidos@getDeseosUsuario');
Route::post('getQuitarDeseo','App\Http\Controllers\controladorPedidos@getQuitarDeseo');
Route::post('getDeseosActivos','App\Http\Controllers\controladorPedidos@getDeseosActivos');


Route::post('getPedidoClienteOperador','App\Http\Controllers\controladorPedidos@getPedidoClienteOperador');
Route::post('getCheckoutProducto','App\Http\Controllers\controladorPedidos@getCheckoutProducto');
Route::post('getOrdenesProducto','App\Http\Controllers\controladorPedidos@getOrdenesProducto');

Route::post('getUpdateUsuarioOper','App\Http\Controllers\controladorUsuarios@getUpdateUsuarioOper');
Route::post('getUpdateUsuarioVisit','App\Http\Controllers\controladorUsuarios@getUpdateUsuarioVisit');
Route::post('getActualizarMembresia','App\Http\Controllers\controladorUsuarios@getActualizarMembresia');
Route::post('getOperadorVendedor','App\Http\Controllers\controladorUsuarios@getOperadorVendedor');
Route::post('getQuitarGaleriaVid','App\Http\Controllers\controladorUsuarios@getQuitarGaleriaVid');

Route::post('getMsmUsuario','App\Http\Controllers\controladorUsuarios@getMsmUsuario');
Route::post('getCategoriaUsuario','App\Http\Controllers\controladorUsuarios@getCategoriaUsuario');
Route::post('getMsmUsuarioCont','App\Http\Controllers\controladorUsuarios@getMsmUsuarioCont');
Route::post('getEliminarPefil','App\Http\Controllers\controladorUsuarios@getEliminarPefil');
Route::post('getImgProductoGaleria','App\Http\Controllers\controladorUsuarios@getImgProductoGaleria');
Route::post('getGaleria','App\Http\Controllers\controladorUsuarios@getGaleria');
Route::post('getQuitarGaleria','App\Http\Controllers\controladorUsuarios@getQuitarGaleria');
Route::post('getQuitarProducto','App\Http\Controllers\controladorUsuarios@getQuitarProducto');

Route::post('getImgPerfilGaleria','App\Http\Controllers\controladorUsuarios@getImgPerfilGaleria');
Route::post('getGaleriaPerfil','App\Http\Controllers\controladorUsuarios@getGaleriaPerfil');
Route::post('getQuitarGaleriaPerfil','App\Http\Controllers\controladorUsuarios@getQuitarGaleriaPerfil');

Route::post('getUsuarioContactoVs','App\Http\Controllers\controladorUsuarios@getUsuarioContactoVs');


Route::post('getCodStorage','App\Http\Controllers\controladorDefiniciones@getCodStorage');

Route::post('getPagosPedido','App\Http\Controllers\controladorPagos@getPagosPedido');

Route::post('getMultimedia','App\Http\Controllers\controladorMultimedia@getMultimedia');
Route::post('getMultimediaGaleria','App\Http\Controllers\controladorMultimedia@getMultimediaGaleria');
Route::post('getQuitarMultimedia','App\Http\Controllers\controladorMultimedia@getQuitarMultimedia');

Route::post('updateUserPass', 'App\Http\Controllers\controladorUsuarios@updateUserPass');

Route::post('sendContactForm', 'App\Http\Controllers\controladorUsuarios@sendContactForm');
    });
});



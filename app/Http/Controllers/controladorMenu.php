<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\menu_app;

class controladorMenu extends Controller
{
    public function getMenu(Request $id){
        try {
            $data = $id->all();
            return response()-> json(menu_app::all(),200);
        } catch (\Throwable $th) {
            return response()-> json(['try_catch'=>$th],404);
        }
    }
    public function getMenuParametro(Request $id){
        try {
            $data = $id->all();
            $objeto = menu_app::where('cod_usuario',$data)->first();
            $message = 'error al consultar';
            if ($objeto !== null) {
                return response()-> json($data,200);
            }else{
                return response()-> json(['message'=>$message],404);
            }
            
        } catch (\Throwable $th) {
            return response()-> json(['message'=>$th],404);
        }
    }
}

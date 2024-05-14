<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Storage;

use Carbon\Carbon;

use App\Http\Controllers\controladorHelpers;

use DB;

class limites_perfiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:limites';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limites usuarios';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $helper = new controladorHelpers();
            $usuarios_mmbr =  DB::select('call getUsuarioMiembro()');
            $fecha;
            $inicio;
            $actual;
            $intervalo;
            //limite mesajeria
            $inicio;
           /*  $actual = Carbon::now(); */
            $diferencia;
            foreach ($usuarios_mmbr as $value) {
                
                    $objeto = DB::select('call getMsmUsuario(
                                            "'.$value->cod_usuario.'",
                                            "'.$helper->UNIDAD.'",
                                            "'.$helper->CERO.'",
                                            "'.$helper->CERO.'",
                                            "'.$helper->CERO.'"
                                            )');
                if (count($objeto) >= intval($value->val_msm)) {
                    $fecha =  str_replace ( '/', '-', $objeto[0]->fecha_inicio);
                    $inicio = new DateTime($fecha.' '.$objeto[0]->hora_inicio);
                    $actual = new DateTime();
                    $intervalo = $inicio->diff($actual);
                    if (intval($intervalo->h) >= intval($helper->LIMITE)) {
                        DB::select('call getResetLimitesUsuario(
                            "'.$value->cod_usuario.'"
                            )');
                    }
                }
            }
            //limite swipe
            foreach ($usuarios_mmbr as $value) {
              
                    $objeto = DB::select('call getMsmUsuario(
                                            "'.$value->cod_usuario.'",
                                            "'.$helper->CERO.'",
                                            "'.$helper->CERO.'",
                                            "'.$helper->CERO.'",
                                            "'.$helper->UNIDAD.'"
                                            )');
                if (count($objeto) >= intval($value->val_swipe)) {
                    $fecha =  str_replace ( '/', '-', $objeto[0]->fecha_inicio);
                    $inicio = new DateTime($fecha.' '.$objeto[0]->hora_inicio);
                    $actual = new DateTime();
                    $intervalo = $inicio->diff($actual);
                    if (intval($intervalo->h) >= intval($helper->LIMITE)) {
                        DB::select('call getResetLimitesUsuario(
                            "'.$value->cod_usuario.'"
                            )');
                    }
                }
            }
        } catch (Exception $th) {
            return $th;
        }
    }
}

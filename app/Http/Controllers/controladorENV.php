<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\controladorCorreo;
use App\Mail\controladorMensajes;
use App\Http\Controllers\controladorHelpers;
use Symfony\Component\Console\Output\ConsoleOutput;
use DB;

class controladorENV extends Controller
{
    public function getEnvCorreo($data)
        {
            try {
                $output = new ConsoleOutput();
                $helper = new controladorHelpers();
                $correo = new controladorCorreo($data[0]->cod_env);
                $output->writeln($data[0]->correo.' '.'llego al controlador env antes del mail');
            Mail::to($data[0]->correo)->send($correo);
            $output->writeln($data[0]->correo.' '.'llego al controlador env');
            } catch (Exception $e) {
                $output->writeln($e.' '.'llego al controlador env en el error');
                return $e;
            }
        }
    public function getEnvCorreoSellerRechazoProducto($data)
        {
            try {
                $output = new ConsoleOutput();
                $helper = new controladorHelpers();
                $correo = new controladorMensajes($data[0]->comentarios);
                $output->writeln($data[0]->correo.' '.'llego al controlador env antes del mail');
            Mail::to($data[0]->correo)->send($correo);
            $output->writeln($data[0]->correo.' '.'llego al controlador env');
            } catch (Exception $e) {
                $output->writeln($e.' '.'llego al controlador env en el error');
                return $e;
            }
        }
}

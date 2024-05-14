<?php

namespace App\Mail;

use Illuminate\Http\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Console\Output\ConsoleOutput;

class controladorMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = 'Asunto del correo';
    public $mensaje;

    public function __construct($mensaje)
    {
        $this->mensaje = $mensaje;
    }

    public function build()
    {
       
        return $this->view('mail')
                    ->with('mensaje', $this->mensaje);
    }
}

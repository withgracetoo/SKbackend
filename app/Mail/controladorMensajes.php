<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Console\Output\ConsoleOutput;

class controladorMensajes extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = 'Asunto del correo';
    public $mensaje;

    public function __construct($mensaje)
    {
        $output = new ConsoleOutput();
        
        $this->mensaje = $mensaje;

        $output->writeln($this->mensaje.' '.'llego al controlador correo');
    }

    public function build()
    {
       
        return $this->from('support@swedishknickers.com')
                    ->view('correo/mensajes')
                    ->with('mensaje', $this->mensaje);
    }
}

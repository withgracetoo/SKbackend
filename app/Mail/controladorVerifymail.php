<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Console\Output\ConsoleOutput;

class controladorVerifymail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = 'Verify your email account';
    public $enlace;


    public function __construct($enlace, $hash)
    {
        $this->enlace = $enlace;
        $this->hash = $hash;

    }

    public function build()
    {
       
        return $this->from('support@swedishknickers.com','support@swedishknickers.com')
                    ->view('correo/verify')
                    ->with('enlace', $this->enlace)
                    ->with('hash', $this->hash);
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Console\Output\ConsoleOutput;

class controladorContacto extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = 'Contact from the web';
    public $name;
    public $email;
    public $subjectm;
    public $messages;

    public function __construct($name, $email, $subjectm, $messages)
    {
        $this->name = $name;
        $this->email = $email;
        $this->subjectm = $subjectm;
        $this->messages = $messages;

    }

    public function build()
    {
       
        return $this->from('support@swedishknickers.com')
                    ->view('correo/contacto')
                    ->with('name', $this->name)
                    ->with('email', $this->email)
                    ->with('subjectm', $this->subjectm)
                    ->with('messages', $this->messages);
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nome;
    public $codigo;
    public $expiracao;

    public function __construct($nome, $codigo, $expiracao = 15)
    {
        $this->nome = $nome;
        $this->codigo = $codigo;
        $this->expiracao = $expiracao;
    }

    public function build()
    {
        return $this->subject('Código de verificação de e-mail')
                    ->view('emails.verify-email');
    }
}

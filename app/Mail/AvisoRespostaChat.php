<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AvisoRespostaChat extends Mailable
{
    use Queueable, SerializesModels;

    public $chat;

    public function __construct($chat)
    {
        $this->chat = $chat;
    }

    public function build()
    {
        return $this->subject('Sua solicitação foi respondida!')
                    ->markdown('emails.aviso_resposta_chat');
    }
}

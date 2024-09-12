<?php
namespace App\Mail;

use App\Models\Chat;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SolicitacaoRespondidaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $chat;

    public function __construct(Chat $chat)
    {
        $this->chat = $chat;
    }

    public function build()
    {
        return $this->subject('Sua solicitação foi respondida')
                    ->view('emails.solicitacao_respondida')
                    ->with(['chat' => $this->chat]);
    }
}

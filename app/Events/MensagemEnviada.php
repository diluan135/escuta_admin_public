<?php
namespace App\Events;

use App\Models\Mensagem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MensagemEnviada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $mensagem;
    public $usuario_id;

    public function __construct(Mensagem $mensagem, $usuario_id)
    {
        $this->mensagem = $mensagem;
        $this->usuario_id = $usuario_id; // Armazena o ID do usuário
    }

    public function broadcastOn()
    {
        return new Channel($this->usuario_id);
    }

    public function broadcastAs()
    {
        return $this->usuario_id;
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->mensagem->id,
            'admin_id' => $this->mensagem->admin_id,
            'usuario_id' => $this->usuario_id, // Envia o ID do usuário no payload
            'chat_id' => $this->mensagem->chat_id,
            'mensagem' => $this->mensagem->mensagem,
            'enviado_em' => $this->mensagem->created_at ? $this->mensagem->created_at->toDateTimeString() : now()->toDateTimeString(),
        ];
    }
}


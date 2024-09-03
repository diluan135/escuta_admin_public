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

    public function __construct(Mensagem $mensagem)
    {
        $this->mensagem = $mensagem;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->mensagem->chat_id);
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->mensagem->id,
            'admin_id' => $this->mensagem->admin_id,
            'chat_id' => $this->mensagem->chat_id,
            'mensagem' => $this->mensagem->mensagem,
            'created_at' => $this->mensagem->created_at->toDateTimeString(),
        ];
    }
}

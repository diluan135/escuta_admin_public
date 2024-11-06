<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log; // Importando a classe Log

class TesteDeConexao implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $mensagem;

    public function __construct($mensagem)
    {
        $this->mensagem = $mensagem;

    }

    public function broadcastOn()
    {
        return new Channel('public');
    }

    public function broadcastAs()
    {
        return 'TesteDeConexao';
    }
}

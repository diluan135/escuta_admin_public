<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Mensagem;
use App\Models\Chat;
use App\Events\MensagemEnviada;
use App\Mail\AvisoRespostaChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail; // Importação do facade Mail
use Illuminate\Support\Facades\Log;  // Importação do facade Log
use App\Mail\SolicitacaoRespondidaMail;
use App\Models\Usuario;
use Carbon\Carbon;

class MensagemController extends Controller
{
    public function index(Request $request)
    {
        try {
            $idChat = $request->input('chat_id');
            $mensagens = Mensagem::where('chat_id', $idChat)->get();
            return response()->json($mensagens);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function enviarMensagem(Request $request)
    {
        try {
            // Validação dos dados
            $validatedData = $request->validate([
                'admin_id' => 'required|integer',
                'chat_id' => 'required|integer|exists:chat,id',
                'mensagem' => 'required|string|max:255',
            ]);

            // Atualiza o status do chat para 'Aberto'
            $chat = Chat::find($validatedData['chat_id']);
            $chat->update(['chat_status' => 'Aberto']);

            // Verifica a última mensagem enviada (de qualquer pessoa) no chat
            $ultimaMensagem = Mensagem::where('chat_id', $chat->id)
                ->latest('enviado_em') // Pega a última mensagem enviada
                ->first();

            // Criação da mensagem
            $mensagem = Mensagem::create([
                'admin_id' => $validatedData['admin_id'],
                'chat_id' => $validatedData['chat_id'],
                'mensagem' => $validatedData['mensagem'],
            ]);

            // Emitir evento via WebSocket
            broadcast(new MensagemEnviada($mensagem))->toOthers();

            // if ($ultimaMensagem) { // Isso aqui é só pra log
            //     $ultimaMensagemEnviadoEm = Carbon::parse($ultimaMensagem->enviado_em);
            //     $tempoDecorrido = now()->diffInMinutes($ultimaMensagemEnviadoEm);

            //     Log::info('Verificando a última mensagem:', [
            //         'timestamp_atual' => now()->toDateTimeString(),
            //         'ultima_mensagem_enviada_em' => $ultimaMensagem->enviado_em,
            //         'tempo_decorrido' => $tempoDecorrido, // Adicionado para depuração
            //         'usuario_id' => $ultimaMensagem->usuario_id,
            //         'admin_id' => $ultimaMensagem->admin_id,
            //     ]);
            // }

            if (
                !$ultimaMensagem ||
                $ultimaMensagem->admin_id === null ||
                now()->diffInMinutes(Carbon::parse($ultimaMensagem->enviado_em)) < -1 //Eu não sei porque mas o diffInMinutes tava sempre saindo negativo, e não consegui inverter isso, por isso esse valor "< -15" significa que ele tem que passar de 15 minutos para mandar outro email, se alguém no futuro estiver vendo este código, me desculpa por ser burro
            ) {
                $usuario = Usuario::find($chat->usuario_id);

                // Envia o email para o usuário
                Mail::to($usuario->email)->send(new AvisoRespostaChat($chat));
            }

            return response()->json($mensagem, 201); // 201 Created
        } catch (\Exception $e) {
            Log::error('Erro ao enviar mensagem: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao enviar mensagem.'], 500);
        }
    }
}

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
            $chat->update(['visualizado_usuario' => 1]);

            
            $mensagem = Mensagem::create([
                'admin_id' => $validatedData['admin_id'],
                'chat_id' => $validatedData['chat_id'],
                'mensagem' => $validatedData['mensagem'],
            ]);
            
            $usuario = Usuario::find($chat->usuario_id);
            Log::info($usuario);
            $mensagem->usuario = $usuario->id;
            Log::info($mensagem);
            // Log::info('mensagem:', $mensagem);
            broadcast(new MensagemEnviada($mensagem, $usuario->id))->toOthers();

            // Verifica a última mensagem enviada (de qualquer pessoa) no chat
            $ultimaMensagem = Mensagem::where('chat_id', $chat->id)
                ->latest('enviado_em') // Pega a última mensagem enviada
                ->first();

            // Criação da mensagem

            if (
                !$ultimaMensagem ||
                $ultimaMensagem->admin_id === null ||
                now()->diffInMinutes(Carbon::parse($ultimaMensagem->enviado_em)) < -1 
            ) {

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

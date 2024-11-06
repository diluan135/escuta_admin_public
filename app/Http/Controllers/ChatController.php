<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function index(Request $request)
    {   
        $data = Chat::where('chat_status', 'Nova solicitação')->get();
        return response()->json($data);
    }

    public function aberto(Request $request)
    {   
        $data = Chat::where('chat_status', 'Aberto')->get();
        return response()->json($data);
    }

    public function fechado(Request $request)
    {   
        $data = Chat::where('chat_status', 'Fechado')->get();            //DEIXAR TUDO EM DB TABLE
        return response()->json($data);
    }

    public function newChat(Request $request)
    {
        // Validação dos dados da requisição
        $validatedData = $request->validate([
            'usuario_id' => 'required|integer|exists:usuario,id',
            'tipo' => 'required|string|max:255',
            'assunto' => 'required|string|max:255',
        ]);

        // Criação de um novo registro de chat
        $chat = Chat::create([
            'usuario_id' => $validatedData['usuario_id'],
            'tipo' => $validatedData['tipo'],
            'assunto' => $validatedData['assunto'],
            'linha' => $request->input('linha'),
            'chat_status' => 'Nova solicitação',
        ]);

        // Retorno de uma resposta JSON com o novo registro criado
        return response()->json($chat, 201); // 201 Created
    }

    public function fecharChat(Request $request){
        $chat = Chat::find($request->input('chat_id'));
        $chat->update(['chat_status' => 'Fechado']);
        $chat->update(['encerrado_em' => date('Y-m-d H:i:s')]);

        return response()->json('Chat fechado com sucesso', 200);
    }

    public function marcarVisualizado(Request $request){
        $chat = Chat::find($request->input('chat_id'));
        Log::info($chat);
        $chat->update(['visualizado_adm' => 0]);
        Log::info('após update: ' . $chat);

        return response()->json('Chat visualizado com sucesso', 200);
    }
}

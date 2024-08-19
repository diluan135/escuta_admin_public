<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Mensagem;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            $validatedData = $request->validate([
                'admin_id' => 'required|integer',
                'chat_id' => 'required|integer|exists:chat,id',
                'mensagem' => 'required|string|max:255',
            ]);

            $mensagem = Mensagem::create([
                'admin_id' => $validatedData['admin_id'],
                'chat_id' => $validatedData['chat_id'],
                'mensagem' => $validatedData['mensagem'],
            ]);

            $chat = Chat::find($validatedData['chat_id']);
            $chat->update(['chat_status' => 'Aberto']);

            return response()->json($mensagem, 201); // 201 Created
        } catch (\Exception $e) {
            Log::error('Erro ao enviar mensagem: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao enviar mensagem.'], 500);
        }
    }
}

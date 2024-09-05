<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ChatPublicar;
use App\Models\MensagemPublicar;
use Illuminate\Http\Request;

class FAQController extends Controller
{
    public function index()
    {
        $response = ChatPublicar::where('publicado', '1')->get();
        return response()->json($response);
    }

    public function getMensagensPublicadas(Request $request)
    {
        $response = MensagemPublicar::where('chatPublicado_id', $request->input('chat_id'))->get();
        return response()->json($response);
    }

    public function publicarChat(Request $request)
    {
        // Verifica se o chat_id já foi publicado
        $chatPublicadoExistente = ChatPublicar::where('chat_id', $request->input('chat_id'))->first();

        if ($chatPublicadoExistente) {
            // Se já existir, retorna uma mensagem de erro
            return response()->json([
                'message' => 'Este chat já foi publicado.',
            ], 400); // Código 400 para indicar uma requisição inválida
        }

        // Cria o registro se o chat_id não estiver em uso
        $chatPublicado = ChatPublicar::create([
            'tipo' => $request->input('tipo'),
            'assunto' => $request->input('assunto'),
            'linha' => $request->input('linha'),
            'publicado' => '1',
            'chat_id' => $request->input('chat_id'),
        ]);

        foreach ($request->input('mensagens') as $mensagem) {
            MensagemPublicar::create([
                'admin_id' => $mensagem['admin_id'],
                'chatPublicado_id' => $chatPublicado->id,
                'mensagem' => $mensagem['mensagem'],
                'publicado' => $mensagem['publicado'],
                'enviada_em' => now(),
            ]);
        }

        return response()->json(['message' => 'Chat publicado com sucesso!']);
    }
}

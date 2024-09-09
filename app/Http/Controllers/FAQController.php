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
        $response = ChatPublicar::get();
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

    public function ativarChat(Request $request){
        ChatPublicar::where('id', $request->input('chat_id'))->update(['publicado' => 1]);
        return response()->json(['message' => 'Chat ativado com sucesso']);
    }

    public function desativarChat(Request $request){
        ChatPublicar::where('id', $request->input('chat_id'))->update(['publicado' => 0]);
        return response()->json(['message' => 'Chat desativado com sucesso!']);
    }


    public function atualizarTitulo(Request $request)
    {

        $chatPublicado = ChatPublicar::where('id', $request->input('chat_id'))->first();

        if ($chatPublicado) {
            // Atualiza o título (assunto) do chat
            $chatPublicado->update([
                'assunto' => $request->input('novo_titulo'),
            ]);

            return response()->json(['message' => 'Título atualizado com sucesso!']);
        }

        return response()->json(['message' => 'Chat não encontrado.'], 404);
    }


    public function atualizarMensagens(Request $request)
    {
        // Valida a requisição para garantir que o 'chat_id' e as 'mensagens' estejam presentes
        $request->validate([
            'chat_id' => 'required|exists:chatPublicado,chat_id',
            'mensagens' => 'required|array',
            'mensagens.*.id' => 'required|exists:mensagemPublicar,id', // Verifica se cada mensagem existe
            'mensagens.*.mensagem' => 'required|string',
            'mensagens.*.publicado' => 'required|boolean',
        ]);

        // Busca o chat publicado pelo 'chat_id'
        $chatPublicado = ChatPublicar::where('chat_id', $request->input('chat_id'))->first();

        if (!$chatPublicado) {
            return response()->json(['message' => 'Chat não encontrado.'], 404);
        }

        // Percorre as mensagens e atualiza cada uma
        foreach ($request->input('mensagens') as $mensagemData) {
            $mensagem = MensagemPublicar::find($mensagemData['id']);

            if ($mensagem) {
                // Atualiza os dados da mensagem
                $mensagem->update([
                    'mensagem' => $mensagemData['mensagem'],
                    'publicado' => $mensagemData['publicado'],
                ]);
            }
        }

        return response()->json(['message' => 'Mensagens atualizadas com sucesso!']);
    }
}

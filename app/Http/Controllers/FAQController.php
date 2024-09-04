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

    public function getMensagensPublicadas(Request $request){
        $response = MensagemPublicar::where('chatPublicado_id', $request->input('id'))->get();
        return response()->json($response);
    }

    public function publicarChat(Request $request)
    {

        $chatPublicado = ChatPublicar::create([
            'tipo' => $request->input('tipo'),
            'assunto' => $request->input('assunto'),
            'linha' => $request->input('linha'),
            'publicado' => '1'
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

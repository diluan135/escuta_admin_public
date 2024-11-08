<?php

namespace App\Http\Controllers;

use App\Models\FAQ;
use Illuminate\Http\Request;

class FAQController extends Controller
{
    public function index()
    {
        $faqs = FAQ::all();
        return response()->json($faqs, 200);
    }
    public function criarFAQ(Request $request)
    {
        $faq = new FAQ();
        $faq->titulo = $request->titulo;
        $faq->descricao = $request->descricao;
        $faq->publicar = 1;  // Padrão: FAQ publicada
        $faq->servidor_id = $request->idServidor;  // Supondo que você esteja utilizando essa chave para relação
        $faq->save();

        return response()->json(['message' => 'FAQ criada com sucesso!', 'faq' => $faq], 200);
    }

    // Atualizar FAQ
    public function atualizarFAQ(Request $request, $id)
    {
        $faq = FAQ::find($id);
        if (!$faq) {
            return response()->json(['message' => 'FAQ não encontrada.'], 404);
        }

        $faq->titulo = $request->titulo;
        $faq->descricao = $request->descricao;
        $faq->save();

        return response()->json(['message' => 'FAQ atualizada com sucesso!', 'faq' => $faq], 200);
    }

    // Alterar o status de publicação da FAQ
    public function alterarStatus($id)
    {
        $faq = FAQ::find($id);
        if (!$faq) {
            return response()->json(['message' => 'FAQ não encontrada.'], 404);
        }

        $faq->publicar = !$faq->publicar; // Alterna o status
        $faq->save();

        return response()->json(['message' => 'Status de publicação alterado!', 'faq' => $faq], 200);
    }

    // Excluir FAQ
    public function excluirFAQ($id)
    {
        $faq = FAQ::find($id);
        if (!$faq) {
            return response()->json(['message' => 'FAQ não encontrada.'], 404);
        }

        $faq->delete();

        return response()->json(['message' => 'FAQ excluída com sucesso!'], 200);
    }

    // Listar todas as FAQ's
    public function listarFAQ()
    {
        $faqs = FAQ::all();
        return response()->json($faqs, 200);
    }
}

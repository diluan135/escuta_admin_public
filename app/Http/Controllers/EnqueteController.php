<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Enquete;
use App\Models\OpcoesEnquete;
use Illuminate\Http\Request;

class EnqueteController extends Controller
{
    public function index()
    {
        $data = Enquete::all();
        foreach ($data as $enquete) {
            $enquete->opcoes = OpcoesEnquete::where('enquete_id', $enquete->id)->get();
        }
        return $data;
    }

    
    public function criarEnquete(Request $request)
    {
        // Criando a enquete e armazenando a instância criada
        $enquete = Enquete::create([
            'admin_id' => $request['admin_id'],     
            'titulo' => $request['titulo'],
            'descricao' => $request['descricao'],
            'encerra_em' => $request['encerra_em']
        ]);

        // Iterando sobre as opções e criando as entradas correspondentes
        foreach ($request['opcoes'] as $opcao) {
            OpcoesEnquete::create([
                'enquete_id' => $enquete->id, // Associando com o id da enquete criada
                'opcao' => $opcao['texto'],  // Texto da opção
                'cor' => $opcao['cor']       // Cor da opção
            ]);
        }

        return response()->json(['message' => 'Enquete criada com sucesso!']);
    }
}

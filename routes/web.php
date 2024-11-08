<?php

use App\Events\MyEvent;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\EnqueteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LinhasController;
use App\Http\Controllers\MensagemController;
use App\Http\Controllers\TestController;
use App\Events\TesteDeConexao;
use App\Http\Controllers\FAQController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


// ------------------------------------------- WEBSOCKETS -----------------------------------------------------


Route::middleware(['web'])->post('/test', function (Request $request) {
    Log::info('CSRF Token Test', ['token' => $request->header('X-CSRF-TOKEN')]);
    return response()->json(['status' => 'CSRF token test passed']);
});

Route::post('/api/teste-conexao', function (Request $request) {
    $mensagem = $request->input('mensagem', 'Mensagem padrão');
    event(new MyEvent($mensagem));
    return response()->json(['status' => 'Evento de conexão enviado']);
});


// ------------------------------------------- OUTRAS ROTAS -----------------------------------------------------

Route::get('/', [HomeController::class, 'index'])->name('home');    //Redireciona para home
Route::get('api/linhas', [LinhasController::class, 'index']);       //Puxa as linhas (Utiliza outra BD)
Route::post('/api/test-event', [TestController::class, 'sendEvent']);
Route::get('/api/usuario/get', [LoginController::class, 'get']);


// ------------------------------------------- ROTAS DE ENQUETES -----------------------------------------------------

Route::get('/api/enquetes', [EnqueteController::class, 'index']);
Route::post('/api/enquete/criarEnquete', [EnqueteController::class, 'criarEnquete']);

// ------------------------------------------- ROTAS DE CHATS & MENSAGENS -----------------------------------------------------

Route::get('/api/chat', [ChatController::class, 'index']);
Route::get('/api/chat/abertos', [ChatController::class, 'aberto']);
Route::get('/api/chat/fechados', [ChatController::class, 'fechado']);
Route::post('/api/chat/newChat', [ChatController::class, 'newChat']);
Route::post('/api/chat/fecharChat', [ChatController::class, 'fecharChat']);
Route::post('/api/chat/visualizar', [ChatController::class, 'marcarVisualizado']);

Route::post('/api/mensagem/enviarMensagem', [MensagemController::class, 'enviarMensagem']);
Route::get('/api/mensagem', [MensagemController::class, 'index']);


// ------------------------------------------- ROTAS DE FAQ -----------------------------------------------------

Route::get('api/FAQ', [FAQController::class, 'index']);
Route::post('api/FAQ/criarFAQ', [FAQController::class, 'criarFAQ']);
Route::get('api/FAQ/listarFAQ', [FAQController::class, 'listarFAQ']);
Route::put('api/FAQ/atualizarFAQ/{id}', [FAQController::class, 'atualizarFAQ']);
Route::put('api/FAQ/alterarStatus/{id}', [FAQController::class, 'alterarStatus']);
Route::delete('api/FAQ/excluirFAQ/{id}', [FAQController::class, 'excluirFAQ']);


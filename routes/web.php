<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\EnqueteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LinhasController;
use App\Http\Controllers\MensagemController;
use App\Http\Controllers\TestController;
use App\Events\TesteDeConexao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


Route::middleware(['web'])->post('/test', function (Request $request) {
    Log::info('CSRF Token Test', ['token' => $request->header('X-CSRF-TOKEN')]);
    return response()->json(['status' => 'CSRF token test passed']);
});



Route::post('/api/teste-conexao', function (Request $request) {
    $mensagem = $request->input('mensagem', 'Mensagem padrão');
    event(new TesteDeConexao($mensagem));
    return response()->json(['status' => 'Evento de conexão enviado']);
});


// ------------------------------------------- OUTRAS ROTAS -----------------------------------------------------

Route::get('/', [HomeController::class, 'index'])->name('home');    //Redireciona para home
Route::get('api/linhas', [LinhasController::class, 'index']);       //Puxa as linhas (Utiliza outra BD)
Route::post('/api/test-event', [TestController::class, 'sendEvent']);


// ------------------------------------------- ROTAS DE ENQUETES -----------------------------------------------------

Route::get('/api/enquetes', [EnqueteController::class, 'index']);
Route::post('/api/enquete/criarEnquete', [EnqueteController::class, 'criarEnquete']);

// ------------------------------------------- ROTAS DE CHATS & MENSAGENS -----------------------------------------------------

Route::get('/api/chat', [ChatController::class, 'index']);
Route::get('/api/chat/abertos', [ChatController::class, 'aberto']);
Route::get('/api/chat/fechados', [ChatController::class, 'fechado']);
Route::post('/api/chat/newChat', [ChatController::class, 'newChat']);
Route::post('/api/chat/fecharChat', [ChatController::class, 'fecharChat']);

Route::post('/api/mensagem/enviarMensagem', [MensagemController::class, 'enviarMensagem']);
Route::get('/api/mensagem', [MensagemController::class, 'index']);


// ------------------------------------------- EXCLUIR ANTES DE UPAR -----------------------------------------------------

// Route::middleware('auth')->get('/api/user', function () {
// });

// ------------------------------------------- ROTAS DE LOGIN -----------------------------------------------------

// Route::controller(LoginController::class)->group(function () {
//     Route::get('/login', 'index')->name('login.index');
//     Route::post('/login', 'store')->name('login.store');
//     Route::post('/logout', 'destroy')->name('login.destroy');
//     Route::get('/registro', 'showRegistroForm')->name('login.registro.form'); // Adiciona esta rota para exibir o formulário
//     Route::post('/registro', 'registro')->name('login.registro');
// });

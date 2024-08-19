<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\EnqueteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LinhasController;
use App\Http\Controllers\MensagemController;

// ------------------------------------------- OUTRAS ROTAS -----------------------------------------------------

Route::get('/', [HomeController::class, 'index'])->name('home');    //Redireciona para home
Route::get('api/linhas', [LinhasController::class, 'index']);       //Puxa as linhas (Utiliza outra BD)

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

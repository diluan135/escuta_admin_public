<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

class LoginController extends Controller
{
    public function index()
    {
        return view('login');
    }

    public function get(Request $request){
        $usuarios = Usuario::get(['id', 'name', 'sobrenome', 'email', 'CPF', 'created_at']);

        return response()->json($usuarios);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cpf' => 'required|cpf_format',
            'password' => 'required',
        ], [
            'cpf.required' => 'O CPF é obrigatório',
            'cpf.cpf_format' => 'O CPF deve ter um formato válido',
            'password.required' => 'A senha é obrigatória',
        ]);

        $cpf = $request->input('cpf');
        $password = $request->input('password');

        // Encontrar o usuário com o CPF fornecido
        $user = Usuario::where('CPF', $cpf)->first();

        if ($user && Hash::check($password, $user->password)) {
            // Senha correta, realizar login
            Auth::login($user);
            return redirect()->route('home')->with('success', 'Login bem-sucedido');
        } else {
            // Senha incorreta ou usuário não encontrado
            return redirect()->route('home')->withErrors(['error' => 'CPF ou senha estão incorretos']);
        }
    }

    public function destroy()
    {
        Auth::logout();
        return redirect()->route('home');
    }

    public function showRegistroForm()
    {
        return view('registro'); // Certifique-se de que este arquivo está em resources/views/registro.blade.php
    }

    public function registro(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sobrenome' => 'required|string|max:255',
            'cpf' => 'required|string|cpf_format|unique:usuario,CPF',
            'email' => 'required|string|email|max:255|unique:usuario,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'O nome é obrigatório',
            'sobrenome.required' => 'O sobrenome é obrigatório',
            'cpf.required' => 'O CPF é obrigatório',
            'cpf.cpf_format' => 'O CPF deve ter um formato válido',
            'cpf.unique' => 'O CPF já está em uso',
            'email.required' => 'O email é obrigatório',
            'email.email' => 'O email deve ser válido',
            'email.unique' => 'O email já está em uso',
            'password.required' => 'A senha é obrigatória',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres',
            'password.confirmed' => 'A confirmação da senha não coincide',
        ]);

        $user = new Usuario();
        $user->name = $request->input('name');
        $user->sobrenome = $request->input('sobrenome');
        $user->CPF = $request->input('cpf');
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password'));
        $user->save();

        return redirect()->route('home')->with('success', 'Usuário registrado com sucesso');
    }
}

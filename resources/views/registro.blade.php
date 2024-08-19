<!DOCTYPE html>
<html>
<head>
    <title>Registro</title>
</head>
<body>
    <h1>Registro</h1>

    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login.registro') }}">
        @csrf
        <div>
            <label for="name">Nome:</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}">
        </div>
        <div>
            <label for="sobrenome">Sobrenome:</label>
            <input type="text" id="sobrenome" name="sobrenome" value="{{ old('sobrenome') }}">
        </div>
        <div>
            <label for="cpf">CPF:</label>
            <input type="text" id="cpf" name="cpf" value="{{ old('cpf') }}">
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}">
        </div>
        <div>
            <label for="password">Senha:</label>
            <input type="password" id="password" name="password">
        </div>
        <div>
            <label for="password_confirmation">Confirme a Senha:</label>
            <input type="password" id="password_confirmation" name="password_confirmation">
        </div>
        <div>
            <button type="submit">Registrar</button>
        </div>
    </form>
</body>
</html>

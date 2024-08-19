<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash; // Adicione esta linha
use App\Models\Usuario;

class EncryptPasswords extends Command
{
    protected $signature = 'encrypt:passwords';

    protected $description = 'Encrypt existing plain text passwords';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $usuarios = Usuario::all();

        foreach ($usuarios as $usuario) {
            if (!password_verify('dummy_password', $usuario->password)) {
                $usuario->password = bcrypt($usuario->password);
                $usuario->save();
            }
        }

        $this->info('All passwords have been encrypted successfully.');
    }
}

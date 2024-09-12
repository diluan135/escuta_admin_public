<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\SolicitacaoRespondidaMail; // Certifique-se de usar o Mailable correto

class TestEmail extends Command
{
    protected $signature = 'email:test';
    protected $description = 'Test email configuration';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $testEmail = 'diluan135@gmail.com'; // Alvo para teste
        Mail::raw('Este é um teste de envio de e-mail.', function ($message) use ($testEmail) {
            $message->to($testEmail)
                    ->subject('Teste de E-mail Laravel');
        });

        $this->info('E-mail de teste enviado com sucesso!');
    }
}

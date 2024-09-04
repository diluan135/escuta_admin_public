<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MensagemPublicar extends Model
{
    protected $table = 'mensagemPublicada';

    protected $fillable = [
        'admin_id',
        'chatPublicado_id',
        'mensagem',
        'enviada_em',
        'publicado',
    ];

    public $timestamps = false;

    const CREATED_AT = 'enviada_em';
}

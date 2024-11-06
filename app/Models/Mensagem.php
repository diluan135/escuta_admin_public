<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mensagem extends Model
{
    protected $table = 'mensagem';

    protected $fillable = [
        'usuario_id',
        'chat_id',
        'admin_id',
        'mensagem',
        'visualizado'
    ];

    public $timestamps = true;

    const CREATED_AT = 'enviado_em';

}

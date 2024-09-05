<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatPublicar extends Model
{
    protected $table = 'chatPublicado';

    protected $fillable = [
        'tipo', 'assunto', 'linha', 'publicado', 'chat_id'
    ];

    public $timestamps = false;
}

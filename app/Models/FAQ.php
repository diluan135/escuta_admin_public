<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FAQ extends Model
{
    protected $table = 'faq';

    protected $fillable = [
        'servidor_id', 'titulo', 'descricao', 'publicar',
    ];

    public $timestamps = false;
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enquete extends Model
{
    protected $table = 'enquetes';

    protected $fillable = [
        'admin_id',
        'titulo',
        'descricao',
        'encerra_em',
    ];

    public $timestamps = false;

    const CREATED_AT = 'criado_em';

}

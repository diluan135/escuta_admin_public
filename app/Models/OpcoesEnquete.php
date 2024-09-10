<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpcoesEnquete extends Model
{
    protected $table = 'opcoes_enquete';

    protected $fillable = [
        'enquete_id',
        'opcao',
        'cor'
    ];

    public $timestamps = false;

}

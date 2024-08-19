<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Linhas extends Model
{
    protected $connection = 'mysql_bilhetagem';
    protected $table = 'linhas_uso_diario';
}

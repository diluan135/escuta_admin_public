<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voto extends Model
{
    use HasFactory;

    // O nome da tabela associada ao modelo
    protected $table = 'votos';

    // O nome da chave primária
    protected $primaryKey = 'id';

    // Se o modelo usa auto-incremento para a chave primária
    public $incrementing = true;

    // O tipo de dado da chave primária
    protected $keyType = 'int';

    // Se os timestamps não são gerenciados pelo Eloquent
    public $timestamps = false;

    // Atributos que podem ser preenchidos em massa
    protected $fillable = [
        'usuario_id',
        'enquetes_id',
        'opcao_id',
        'voto_em',
    ];

    // Definindo a relação com o modelo Usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Definindo a relação com o modelo Enquete
    public function enquete()
    {
        return $this->belongsTo(Enquete::class, 'enquetes_id');
    }

    // Definindo a relação com o modelo Opcao
    public function opcao()
    {
        return $this->belongsTo(Opcao::class, 'opcao_id');
    }
}

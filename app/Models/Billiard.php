<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Billiard extends Model
{
    //
    use HasFactory;
    protected $table = 'rental';
    protected $fillable = ['id', 'id_player', 'lama_waktu', 'waktu_mulai', 'waktu_akhir', 'no_meja', 'status'];
    protected $casts = [
        'waktu_akhir' => 'datetime',
        'waktu_mulai' => 'datetime',
    ];
}

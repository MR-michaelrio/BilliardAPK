<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HargaRental extends Model
{
    use HasFactory;
    protected $table = 'harga_rental';
    protected $fillable = ['id', 'harga', 'jenis'];
    public $timestamps = false;
}

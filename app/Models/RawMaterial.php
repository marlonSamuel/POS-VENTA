<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    use HasFactory;

    protected $table = "raw_materials";
    protected $fillable = [
        "id",
        "name",
        "description",
        "price",
        "image",
        "stock"
    ];
}

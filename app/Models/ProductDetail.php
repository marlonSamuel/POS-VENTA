<?php

namespace App\Models;

use App\Models\Product;
use App\Models\RawMaterial;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDetail extends Model
{
    use HasFactory;
    protected $table = "product_details";
    protected $fillable = [
        "id",
        "product_id",
        "raw_material_id",
        "quantity",
        "cost_price"
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function raw_material()
    {
        return $this->belongsTo(RawMaterial::class);
    }
}

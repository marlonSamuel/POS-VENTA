<?php

namespace App\Models;

use App\Models\ProductDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = "products";
    protected $fillable = [
        "id",
        "category_product_id",
        "name",
        "price",
        "cost_price",
        "stock",
        "description",
        "photo"
    ];


    public function detail()
    {
        return $this->hasMany(ProductDetail::class);
    }
}

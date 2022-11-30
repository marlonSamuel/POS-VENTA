<?php

namespace App\Models;

use App\Models\Purchase;
use App\Models\RawMaterial;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
    use HasFactory;
        protected $table = "purchase_details";
    protected $fillable = [
        "id",
        "purchase_id",
        "raw_material_id",
        "quantity",
        "purchase_price",
        "discount"
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function raw_material()
    {
        return $this->belongsTo(RawMaterial::class);
    }
}

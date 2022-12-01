<?php

namespace App\Models;

use App\Models\SaleDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;
    protected $table = "sales";
    protected $fillable = [
        "id",
        "total",
        "description",
        "discounted",
        "discount_reason",
        "date",
        "cancel"
    ];

    public function detail()
    {
        return $this->hasMany(SaleDetail::class);
    }
}

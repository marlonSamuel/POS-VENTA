<?php

namespace App\Models;

use App\Models\Provider;
use App\Models\PurchaseDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;
    protected $table = "purchases";
    protected $fillable = [
        "id",
        "provider_id",
        "total",
        "description",
        "invoice",
        "date",
        "cancel"
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }


    public function detail()
    {
        return $this->hasMany(PurchaseDetail::class);
    }
}

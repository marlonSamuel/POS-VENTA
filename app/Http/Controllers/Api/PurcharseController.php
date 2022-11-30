<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Http\storeAs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PurcharseController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $data = Purchase::with('provider','detail.raw_material')
                ->where('cancel','0')
                ->orderByDesc('date')
                ->get();
        return $this->showAll($data);
    }

    public function show($id)
    {
        $purchase = Purchase::where('id',$id)->with('provider','detail');
            if(is_null($purchase)) return $this->errorResponse('no existe producto',421);
    }


    public function store(Request $request)
    {
        $request->validate([
            'provider_id'=>'required',
            'description' => 'required',
            'total' => 'required',
            'invoice' => 'required|mimes:png,jpg,jpeg,webp',
            'detail' => 'required'
        ]);

        $data = $request->all();

        DB::beginTransaction();
        $purchase = Purchase::create($data);

        //si la imagen viene actualizamos
        if(!is_null($request->invoice) && $request->invoice !== "" && $request->invoice !== "null"){
            $folder = 'purchases';
            $name = $purchase->id.'-'.$request->invoice->getClientOriginalName();
            $invoice = 'products/'.$request->invoice->storeAs($folder, $name);
            $purchase->invoice = $invoice;
            $purchase->save();
        }

        $detail = json_decode($request->detail);

        foreach ($detail as $value) {
            $raw_material = RawMaterial::find($value->raw_material_id);
            if(is_null($raw_material)) return $this->errorResponse('no existe materia prima',422);

            PurchaseDetail::create([
                'purchase_id' => $purchase->id,
                'raw_material_id' => $value->raw_material_id,
                'quantity' => $value->quantity,
                'purchase_price' => $value->purchase_price,
                'discount' => $value->discount
            ]);

            $raw_material->stock = $raw_material->stock + $value->quantity;
            $raw_material->save();
        }

        DB::commit();

        return $this->showOne($purchase,201);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
            $purchase = Purchase::find($id);
            if(is_null($purchase)) return $this->errorResponse('no existe producto',421);
            $p_detail = $purchase->detail()->get();
            
            foreach ($p_detail as $value) {
                $raw_material = RawMaterial::find($value->raw_material_id);
                $raw_material->stock = $raw_material->stock-$value->quantity;
                $raw_material->save();
            }
            $purchase->cancel = true;
            $purchase->save();
        DB::commit();
        return $this->showOne($purchase,201);
    }
}
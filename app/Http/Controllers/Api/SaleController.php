<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $data = Sale::with('detail.product')
                ->where('cancel','0')
                ->orderByDesc('date')
                ->get();
        return $this->showAll($data);
    }

    public function show($id)
    {
        $sale = Sale::where('id',$id)->with('detail.product');
            if(is_null($sale)) return $this->errorResponse('no existe producto',421);

        return $this->showOne($sale);
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required',
            'total' => 'required',
            'date' => 'required',
            'detail' => 'required',
            'discounted' => 'required'
        ]);

        $data = $request->all();

        DB::beginTransaction();
        $sale = Sale::create($data);

        $detail = $request->detail;

        foreach ($detail as $value) {
            $product = Product::find($value['product_id']);
            if(is_null($product)) return $this->errorResponse('no existe producto',422);

            if($product->stock < $value['quantity']) return $this->errorResponse('stock insuficiente de '.$product->name,422);

            SaleDetail::create([
                'sale_id' => $sale->id,
                'product_id' => $value['product_id'],
                'quantity' => $value['quantity'],
                'sale_price' => $value['sale_price'],
                'discount' => $value['discount']
            ]);

            $product->stock = $product->stock - $value['quantity'];
            $product->save();
        }

        DB::commit();

        return $this->showOne($sale,201);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
            $sale = Sale::find($id);
            if(is_null($sale)) return $this->errorResponse('no existe venta',421);
            $p_detail = $sale->detail()->get();
            
            foreach ($p_detail as $value) {
                $product = Product::find($value->product_id);
                $product->stock = $product->stock+$value->quantity;
                $product->save();
            }
            $sale->cancel = true;
            $sale->save();
        DB::commit();
        return $this->showOne($product,201);
    }
}

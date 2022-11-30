<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Http\storeAs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Exception;

class ProductController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $search = Request()->search;
        if(!is_null($search) && $search !== 'undefined')
        {
            $data = DB::table('products')
                    ->select('*')
                    ->where('name', 'like', '%' . $search . '%')
                    ->orderByDesc('id')
                    ->get();
        }else{
            $data = Product::orderByDesc('id')->get();
        }
        return $this->showAll($data);
    }

    public function show($id)
    {
        $product = Product::where('id',$id)->with('detail.raw_material')->first();
        if(is_null($product)) return $this->errorResponse('Producto no existe',421);
        foreach ($product->detail as $value) {
            $value->price = $value->raw_material->price;
            $value->material_name = $value->raw_material->name;
            $value->stock = $value->raw_material->stock;
            $value->image = $value->raw_material->image;
        }
        return $this->showOne($product,201);
    }


    public function store(Request $request)
    {
        $request->validate([
            'category_product_id'=>'required',
            'name'=>'required|string',
            'description' => 'required',
            'price' => 'required',
            'cost_price'=>'required',
            'photo' => 'required|mimes:png,jpg,jpeg,webp',
            'stock' => 'required',
            'detail' => 'required'
        ]);

        $request->cost_price = collect($request->detail)->sum('price');

        $data = $request->all();

        DB::beginTransaction();
        $product = Product::create($data);

        //si la imagen viene actualizamos
        if(!is_null($request->photo) && $request->photo !== "" && $request->photo !== "null"){
            $folder = 'product';
            $name = $product->id.'-'.$request->photo->getClientOriginalName();
            $photo = 'products/'.$request->photo->storeAs($folder, $name);
            $product->photo = $photo;
            $product->save();
        }

        $detail = json_decode($request->detail);

        foreach ($detail as $value) {
            $raw_material = RawMaterial::find($value->raw_material_id);
            if(is_null($raw_material)) return $this->errorResponse('no existe materia prima',422);

            if($raw_material->stock < $value->quantity) return $this->errorResponse('No existe suficiente materia prima de '.$raw_material->name,422);

            ProductDetail::create([
                'product_id' => $product->id,
                'raw_material_id' => $value->raw_material_id,
                'quantity' => $value->quantity,
                'cost_price' => $value->cost_price
            ]);

            $raw_material->stock = $raw_material->stock - $value->quantity;
            $raw_material->save();
        }

        DB::commit();

        return $this->showOne($product,201);
    }

    public function _update(Request $request)
    {
        $request->validate([
            'id'=>'required',
            'category_product_id'=>'required',
            'name'=>'required|string',
            'description' => 'required',
            'price' => 'required',
            'cost_price'=>'required',
            'stock' => 'required',
            'detail' => 'required',
            'photo' => 'required'
        ]);

        $product = Product::find($request->id);
        if(is_null($product)) return $this->errorResponse('no existe registro',421);

        $data = $request->all();
        DB::beginTransaction();

        $product->category_product_id = $request->category_product_id;
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->cost_price = $request->cost_price;
        $product->stock = $request->stock;

        //si la imagen viene actualizamos
        if($request->hasFile('photo')){
            File::delete($product->photo);
            $folder = 'product';
            $name = $product->id.'-'.$request->photo->getClientOriginalName();
            $photo = 'products/'.$request->photo->storeAs($folder, $name);
            $product->photo = $photo;
            $product->save();
        }

        //actualizar stock y eliminar detalle temporal
        $p_detail = $product->detail()->get();
        foreach ($p_detail as $value) {
            $raw_material = RawMaterial::find($value->raw_material_id);
            $raw_material->stock = $raw_material->stock+$value->quantity;
            $raw_material->save();
        }

        $product->detail()->delete();

        $detail = json_decode($request->detail);

        foreach ($detail as $value) {
            $raw_material = RawMaterial::find($value->raw_material_id);
            if(is_null($raw_material)) return $this->errorResponse('no existe materia prima',422);

            if($raw_material->stock < $value->quantity) return $this->errorResponse('No existe suficiente materia prima de '.$raw_material->name,422);

            ProductDetail::create([
                'product_id' => $product->id,
                'raw_material_id' => $value->raw_material_id,
                'quantity' => $value->quantity,
                'cost_price' => $value->cost_price
            ]);

            $raw_material->stock = $raw_material->stock - $value->quantity;
            $raw_material->save();
        }
        $product->save();

        DB::commit();

        return $this->showOne($product,201);
    }

    public function addOrDecreaseStock(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'option'=>'required'
        ]);

        DB::beginTransaction();

        $product = Product::find($request->id);
        if(is_null($product)) return $this->errorResponse('no existe producto',421);

        $p_detail = $product->detail()->get();

        if($request->option == 'a'){
            $product->stock++;
            foreach ($p_detail as $value) {
                $raw_material = RawMaterial::find($value->raw_material_id);
                if($raw_material->stock < $value->quantity) return $this->errorResponse('No existe suficiente materia prima de '.$raw_material->name.' para la creación de producto',422);

                $raw_material->stock = $raw_material->stock-$value->quantity;
                $raw_material->save();
            }

        }else{
            $product->stock--;
            foreach ($p_detail as $value) {
                $raw_material = RawMaterial::find($value->raw_material_id);
                $raw_material->stock = $raw_material->stock+$value->quantity;
                $raw_material->save();
            }
        }

        $product->save();
        DB::commit();
        return $this->showOne($product,201);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
            $product = Product::find($id);
            if(is_null($product)) return $this->errorResponse('no existe producto',421);
            $p_detail = $product->detail()->get();
            
            foreach ($p_detail as $value) {
                $raw_material = RawMaterial::find($value->raw_material_id);
                $raw_material->stock = $raw_material->stock+$value->quantity;
                $raw_material->save();
            }
            $product->detail()->delete();
            $product->delete();
        DB::commit();
        return $this->showOne($product,201);
    }
}

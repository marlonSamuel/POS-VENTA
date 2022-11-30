<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\storeAs;
use Illuminate\Support\Facades\DB;

class RawMaterialController extends ApiController
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
            $data = DB::table('raw_materials')
                    ->select('*')
                    ->where('name', 'like', '%' . $search . '%')
                    ->orderByDesc('id')
                    ->get();
        }else{
            $data = RawMaterial::orderByDesc('id')->get();
        }
        return $this->showAll($data);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name'=>'required|string',
            'description' => 'required',
            'price' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg,webp'
        ]);
        $data = $request->all();
        DB::beginTransaction();
        $raw_material = RawMaterial::create($data);

        //si la imagen viene actualizamos
        if(!is_null($request->image) && $request->image !== "" && $request->image !== "null"){
            $folder = 'raw-material';
            $name = $raw_material->id.'-'.$request->image->getClientOriginalName();
            $image_name = 'products/'.$request->image->storeAs($folder, $name);
            $raw_material->image = $image_name;
            $raw_material->save();
        }
        DB::commit();

        return $this->showOne($raw_material,201);
    }

    public function show(RawMaterial $rawMaterial)
    {
        return $this->showOne($rawMaterial);
    }


    //update
    public function _update(Request $request)
    {
        $rawMaterial = RawMaterial::find($request->id);
        if(is_null($rawMaterial)) return $this->errorResponse('no existe registro',421);

        $request->validate([
            'name'=>'required|string',
            'description' => 'required',
            'price' => 'required',
            'stock' =>'required'
        ]);

        $rawMaterial->name = $request->name;
        $rawMaterial->description = $request->description;
        $rawMaterial->stock = $request->stock;
        $rawMaterial->price = $request->price;
        $rawMaterial->stock = $request->stock;

        //si la imagen viene actualizamos
        if($request->hasFile('image')){
            File::delete($rawMaterial->image);
            $folder = 'raw-material';
            $name = $rawMaterial->id.'-'.$request->image->getClientOriginalName();
            $image_name = 'products/'.$request->image->storeAs($folder, $name);
            $rawMaterial->image = $image_name;
            $rawMaterial->save();
        }

        $rawMaterial->save();
        return $this->showOne($rawMaterial,201);
    }


    public function destroy(RawMaterial $rawMaterial)
    {
        $rawMaterial->delete();
        return $this->showOne($rawMaterial,201);
    }
}

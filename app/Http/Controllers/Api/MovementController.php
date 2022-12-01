<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Movement;
use Illuminate\Http\Request;

class MovementController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $search = Request()->search;
        $data = Movement::where('movement_type',$search)->get();
        return $this->showAll($data);
    }

    public function indexAll()
    {
        $data = Movement::all();
        return $this->showQuery($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'movement_type'=>'required',
            'date'=>'required',
            'price'=>'required',
            'description'=>'required'
        ]);
        $data = $request->all();
        $movement = Movement::create($data);
        return $this->showOne($movement,201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Movement $movement)
    {
        return $this->showOne($movement);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Movement $movement)
    {
        $request->validate([
            'date'=>'required',
            'price'=>'required',
            'description'=>'required'
        ]);

        $movement->date = $request->date;
        $movement->price = $request->price;
        $movement->description = $request->description;

         if (!$movement->isDirty()) {
            return $this->errorResponse('Se debe especificar al menos un valor diferente para actualizar', 422);
        }

        $movement->save();
        return $this->showOne($movement,201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Movement $movement)
    {
        $movement->delete();
        return $this->showOne($movement,201);
    }
}

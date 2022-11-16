<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;

class ProviderController extends ApiController
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
        $data = Provider::all();
        return $this->showAll($data);
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
            'name'=>'required|string'
        ]);
        $data = $request->all();
        $provider = Provider::create($data);
        return $this->showOne($provider,201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Provider $provider)
    {
        return $this->showOne($provider);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Provider $provider)
    {
        $request->validate([
            'name' => 'required|string'
        ]);

        $provider->name = $request->name;
        $provider->observation = $request->observation;

         if (!$provider->isDirty()) {
            return $this->errorResponse('Se debe especificar al menos un valor diferente para actualizar', 422);
        }

        $provider->save();
        return $this->showOne($provider,201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Provider $provider)
    {
        $provider->delete();
        return $this->showOne($provider,201);
    }
}

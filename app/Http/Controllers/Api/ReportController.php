<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function sales(Request $request)
    {
        $data = DB::table('sales')
                ->whereBetween('date', [$request->from, $request->to])
                ->where('cancel','0')
                ->get();

        $quantity = $data->count();
        $total = $data->sum('total');

        $response = [
            "data" => $data,
            "quantity" => $quantity,
            "total" => $total
        ];

        return $this->showQuery($response,201);
    }

    public function purchases(Request $request)
    {
        $data = DB::table('purchases as s')
                ->join('providers as p','s.provider_id','=','p.id')
                ->select('s.*', 'p.name as provider')
                ->whereBetween('date', [$request->from, $request->to])
                ->where('cancel','0')
                ->get();

        $quantity = $data->count();
        $total = $data->sum('total');

        $response = [
            "data" => $data,
            "quantity" => $quantity,
            "total" => $total
        ];

        return $this->showQuery($response,201);
    }

    public function movements(Request $request)
    {
        $data = DB::table('movements')
                ->whereBetween('date', [$request->from, $request->to])
                ->get();

        $quantity_i = $data->where('movement_type','i')->count(); 
        $quantity_o = $data->where('movement_type','o')->count();

        $total_i = $data->where('movement_type','i')->sum('price'); 
        $total_o = $data->where('movement_type','o')->sum('price');

        $response = [
            "data" => $data,
            "quantity_i" => $quantity_i,
            "quantity_o" => $quantity_o,
            "total_i" => $total_i,
            "total_o" => $total_o
        ];

        return $this->showQuery($response,201);

    }

    public function balance(Request $request)
    {
        $purchases = DB::table('purchases')
                    ->select(DB::raw('count(*) as quantity'),DB::raw('COALESCE(sum(total),0) as total'))
                    ->where('cancel','0')
                    ->whereBetween('date', [$request->from, $request->to])
                    ->first();

        $sales = DB::table('sales')
                    ->select(DB::raw('count(*) as quantity'),DB::raw('COALESCE(sum(total),0) as total'))
                    ->where('cancel','0')
                    ->whereBetween('date', [$request->from, $request->to])
                    ->first();

        $incomes = DB::table('movements')
                    ->select(DB::raw('count(*) as quantity'),DB::raw('COALESCE(sum(price),0) as total'))
                    ->whereBetween('date', [$request->from, $request->to])
                    ->where('movement_type','i')
                    ->first();

        $outcomes = DB::table('movements')
                    ->select(DB::raw('count(*) as quantity'),DB::raw('COALESCE(sum(price),0) as total'))
                    ->whereBetween('date', [$request->from, $request->to])
                    ->where('movement_type','o')
                    ->first();

        $response = [
            "sales" => $sales,
            "purchases" => $purchases,
            "incomes" => $incomes,
            "outcomes" => $outcomes
        ];

        return $this->showQuery($response,201);

    }
}

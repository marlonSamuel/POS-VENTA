<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Movement;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
    }
    

    public function dashboard()
    {
        $year = Carbon::now()->year;
        $init = $year.'-01-01';
        $end = $year.'-12-31';

        $sales = Sale::where('cancel','0')
                ->whereBetween('date', [$init, $end])
                ->sum('total');
        $purchases = Purchase::where('cancel','0')
                ->whereBetween('date', [$init, $end])
                ->sum('total');
        $incomes = Movement::where('movement_type','i')
                ->whereBetween('date', [$init, $end])
                ->sum('price');
        $outcomes = Movement::where('movement_type','o')
                ->whereBetween('date', [$init, $end])
                ->sum('price');

        $products = Product::count();

        $resumen = [
            'sales' => $sales,
            'purchases' => $purchases,
            'incomes' => $incomes,
            'outcomes' => $outcomes,
            'products' => $products
        ];

        return $this->showQuery($resumen);
    }

    public function sales()
    {
        $year = Carbon::now()->year;
        $init = $year.'-01-01';
        $end = $year.'-12-31';

        $data = DB::table('sales')
                ->select(DB::raw('COALESCE(sum(total)) as `total`'), DB::raw("DATE_FORMAT(date, '%m-%Y') as fecha"))
                ->where('cancel','0')
                ->groupBy(DB::raw("DATE_FORMAT(date, '%m-%Y')"))
                ->get();

        return $this->showQuery($data);
    }

    public function purchases()
    {
        $year = Carbon::now()->year;
        $init = $year.'-01-01';
        $end = $year.'-12-31';

        $data = DB::table('purchases')
                ->select(DB::raw('COALESCE(sum(total)) as `total`'), DB::raw("DATE_FORMAT(date, '%m-%Y') as fecha"))
                ->where('cancel','0')
                ->groupBy(DB::raw("DATE_FORMAT(date, '%m-%Y')"))
                ->get();

        return $this->showQuery($data);
    }
}

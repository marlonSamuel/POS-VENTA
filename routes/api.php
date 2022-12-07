<?php
use App\Http\Controllers\Api\CategoryProductController;
use App\Http\Controllers\Api\DashController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProviderController;
use App\Http\Controllers\Api\RawMaterialController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\api\MovementController;
use App\Http\Controllers\api\PurcharseController;
use App\Http\Controllers\api\SaleController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::post('register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::get('auth/logout', [AuthController::class, 'logout']);
Route::get('auth/me', [AuthController::class, 'user']);

Route::resource('providers', ProviderController::class, ['except' => ['create', 'edit']]);
Route::get('providers-all', [ProviderController::class, 'indexAll']);
Route::get('category-products-all', [CategoryProductController::class, 'indexAll']);
Route::resource('category-products', CategoryProductController::class, ['except' => ['create', 'edit']]);
Route::resource('raw-materials', RawMaterialController::class, ['except' => ['create', 'edit','update']]);
Route::post('raw-materials-update', [RawMaterialController::class, '_update']);

Route::resource('productss', ProductController::class, ['except' => ['create', 'edit','update']]);
Route::post('productss-update', [ProductController::class, '_update']);
Route::post('productss-add-or-decrease', [ProductController::class, 'addOrDecreaseStock']);
Route::resource('purchases', PurcharseController::class, ['except' => ['create', 'edit','update']]);
Route::resource('sales', SaleController::class, ['except' => ['create', 'edit','update']]);
Route::resource('movements', MovementController::class, ['except' => ['create', 'edit']]);

//reportes
Route::post('reports-sales', [ReportController::class, 'sales']);
Route::post('reports-purchases', [ReportController::class, 'purchases']);
Route::post('reports-movements', [ReportController::class, 'movements']);
Route::post('reports-balance', [ReportController::class, 'balance']);
Route::post('dashboard', [DashController::class, 'dashboard']);
Route::post('dashboard-sales', [DashController::class, 'sales']);
Route::post('dashboard-purchases', [DashController::class, 'purchases']);
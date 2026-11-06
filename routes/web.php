<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DatabaseController;
require __DIR__.'/auth.php';
Route::middleware('auth')->group(function(){Route::get('/',[DatabaseController::class,'index']);Route::post('/snapshots',[DatabaseController::class,'snapshot'])->middleware('throttle:15,1');Route::post('/targets/{target}/reset',[DatabaseController::class,'reset'])->middleware('throttle:15,1');Route::post('/targets/{target}/seed',[DatabaseController::class,'seed'])->middleware('throttle:15,1');Route::post('/targets/{target}/restore/{snapshot}',[DatabaseController::class,'restore'])->middleware('throttle:15,1');});

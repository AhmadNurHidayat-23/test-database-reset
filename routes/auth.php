<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
Route::middleware('guest')->group(function(){
 Route::get('/login',[AuthController::class,'loginForm'])->name('login');
 Route::post('/login',[AuthController::class,'login'])->middleware('throttle:6,1');
 Route::get('/register',[AuthController::class,'registerForm']);
 Route::post('/register',[AuthController::class,'register'])->middleware('throttle:6,1');
});
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth');

<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
class AuthController extends Controller
{
 public function loginForm() {return $this->page('login');}
 public function registerForm() {abort_if(config('tool.owner_only') && User::exists(),403);return $this->page('register');}
 private function page(string $mode) {
   $props=['mode'=>$mode,'can_register'=>!config('tool.owner_only')||!User::exists()];
   return config('tool.react')?Inertia::render('Auth',$props):view('auth',$props);
 }
 public function login(Request $request) {
   $data=$request->validate(['email'=>'required|email','password'=>'required|string']);
   if(!Auth::attempt($data)) throw ValidationException::withMessages(['email'=>'Email atau password tidak cocok.']);
   $request->session()->regenerate();return redirect()->intended('/');
 }
 public function register(Request $request) {
   $data=$request->validate(['name'=>'required|string|max:100','email'=>'required|email|max:200|unique:users','password'=>'required|string|min:10|max:128|confirmed']);
   $user=DB::transaction(function() use($data) {
     abort_if(config('tool.owner_only')&&User::exists(),403);
     $first=!User::exists(); $user=User::create($data);$user->forceFill(['is_admin'=>$first])->save();return $user;
   },5);
   Auth::login($user);$request->session()->regenerate();return redirect('/');
 }
 public function logout(Request $request) {Auth::logout();$request->session()->invalidate();$request->session()->regenerateToken();return redirect('/login');}
}

<?php
namespace App\Http\Controllers;
use App\Models\{Snapshot,DatabaseOperation};
use App\Services\{TestingTarget,DatabaseManager};
use Illuminate\Http\Request;
use Inertia\Inertia;
class DatabaseController extends Controller {
 private function authorizeOwner(Request $request):void{abort_unless((bool)$request->user()->is_admin,403,'Hanya pemilik aplikasi yang dapat mengelola target testing.');}
 public function index(Request $request,TestingTarget $target){$this->authorizeOwner($request);$state=$target->state();return Inertia::render('App',['target'=>['id'=>TestingTarget::ID,'driver'=>'SQLite','path'=>$target->path(),'state'=>$state],'snapshots'=>Snapshot::where('user_id',$request->user()->id)->latest()->paginate(10,['id','name','sha256','bytes','state','automatic','created_at'],'snapshots_page')->withQueryString(),'history'=>DatabaseOperation::where('user_id',$request->user()->id)->latest()->paginate(10,['*'],'history_page')->withQueryString()]);}
 public function snapshot(Request $request,DatabaseManager $manager){$this->authorizeOwner($request);$data=$request->validate(['name'=>'required|string|max:100','path'=>'prohibited','connection'=>'prohibited']);$manager->snapshot($request->user(),$data['name']);return back()->with('status','Snapshot dibuat dan lolos pemeriksaan integritas serta hash data/schema.');}
 public function reset(Request $request,string $target,DatabaseManager $manager){$this->authorizeOwner($request);$data=$request->validate(['confirmation'=>'required|string|max:100','path'=>'prohibited','connection'=>'prohibited','sql'=>'prohibited']);$manager->reset($request->user(),$target,$data['confirmation']);return back()->with('status','Target testing direset. Snapshot pengaman tersedia dan tabel terverifikasi kosong.');}
 public function seed(Request $request,string $target,DatabaseManager $manager){$this->authorizeOwner($request);$request->validate(['sql'=>'prohibited','path'=>'prohibited','connection'=>'prohibited']);$manager->seed($request->user(),$target);return back()->with('status','Seeder testing bawaan dijalankan pada target terdaftar.');}
 public function restore(Request $request,string $target,Snapshot $snapshot,DatabaseManager $manager){$this->authorizeOwner($request);$data=$request->validate(['confirmation'=>'required|string|max:100','path'=>'prohibited','connection'=>'prohibited','sql'=>'prohibited']);$manager->restore($request->user(),$target,$snapshot,$data['confirmation']);return back()->with('status','Pemulihan selesai. Integritas, foreign key, serta hash data/schema cocok dengan snapshot.');}
}

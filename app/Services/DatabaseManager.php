<?php
namespace App\Services;
use App\Models\{User,Snapshot,DatabaseOperation};
use Database\Seeders\TestingDataSeeder;
use SQLite3;
class DatabaseManager {
 public function __construct(private TestingTarget $target,private SnapshotStore $snapshots,private TargetSchema $schema){}
 private function locked(callable $callback):mixed {
  $this->target->environment(TestingTarget::ID);$path=$this->target->directory().DIRECTORY_SEPARATOR.'.operation.lock';if(is_link($path))$this->target->fail('Symlink lock testing ditolak.');$handle=fopen($path,'c');
  if(!$handle||!flock($handle,LOCK_EX|LOCK_NB))abort(409,'Operasi testing lain sedang berlangsung.');
  try{return $callback();}
  catch(\Illuminate\Validation\ValidationException|\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e){throw $e;}
  catch(\Throwable $e){throw \Illuminate\Validation\ValidationException::withMessages(['operation'=>'Operasi dihentikan: '.$e->getMessage()]);}
  finally{flock($handle,LOCK_UN);fclose($handle);}
 }
 public function snapshot(User $user,string $name):Snapshot {
  return $this->locked(function()use($user,$name){$snapshot=$this->snapshots->create($user,$name);DatabaseOperation::create(['user_id'=>$user->id,'kind'=>'snapshot','status'=>'passed','details'=>['snapshot_id'=>$snapshot->id,'verified'=>true]]);return $snapshot;});
 }
 private function confirmation(string $actual,string $expected):void {if(!hash_equals($expected,$actual))$this->target->fail('Konfirmasi eksplisit tidak cocok. Ketik '.$expected.'.');}
 private function operation(User $user,string $kind,callable $callback):array {
  return $this->locked(function()use($user,$kind,$callback){$before=$this->target->state();$history=DatabaseOperation::create(['user_id'=>$user->id,'kind'=>$kind,'status'=>'running','details'=>['before'=>$before]]);
   try{$details=$callback();$after=$this->target->state();$history->update(['status'=>'passed','details'=>['before'=>$before,'after'=>$after,...$details]]);return $after;}
   catch(\Throwable $e){$history->update(['status'=>'failed','details'=>['before'=>$before,'error'=>$e->getMessage()]]);throw $e;}
  });
 }
 public function reset(User $user,string $id,string $confirmation):array {
  $this->target->environment($id);$this->confirmation($confirmation,'RESET '.TestingTarget::ID);
  return $this->operation($user,'reset',function()use($user){$safety=$this->snapshots->create($user,'Pengaman sebelum reset',true);$db=$this->target->database();try{$this->schema->reset($db);}finally{$db->close();}$after=$this->target->state();if(array_sum($after['counts'])!==0)throw new \RuntimeException('Reset belum terverifikasi kosong.');return ['safety_snapshot'=>$safety->id,'verified'=>true];});
 }
 public function seed(User $user,string $id):array {
  $this->target->environment($id);return $this->operation($user,'seed',function(){$db=$this->target->database();try{(new TestingDataSeeder)->run($db);}finally{$db->close();}return ['seeder'=>TestingDataSeeder::class,'verified'=>true];});
 }
 public function restore(User $user,string $id,Snapshot $snapshot,string $confirmation):array {
  $this->target->environment($id);abort_unless($snapshot->user_id===$user->id,403);$this->confirmation($confirmation,'PULIHKAN '.$snapshot->id);
  return $this->operation($user,'restore',function()use($user,$snapshot){$path=$this->snapshots->verifiedPath($snapshot);$safety=$this->snapshots->create($user,'Pengaman sebelum restore #'.$snapshot->id,true);$source=new SQLite3($path,SQLITE3_OPEN_READONLY);$destination=$this->target->database();try{if(!$source->backup($destination))throw new \RuntimeException('Restore SQLite gagal.');}finally{$source->close();$destination->close();}$after=$this->target->state();if($after['data_hash']!==$snapshot->state['data_hash']||$after['schema_hash']!==$snapshot->state['schema_hash'])throw new \RuntimeException('Hash data/schema setelah pemulihan tidak cocok.');return ['restored_snapshot'=>$snapshot->id,'safety_snapshot'=>$safety->id,'verified'=>true];});
 }
}

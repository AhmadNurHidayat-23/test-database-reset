<?php
namespace App\Services;
use App\Models\{Snapshot,User};
use Illuminate\Support\Str;
use SQLite3;
class SnapshotStore {
 public function __construct(private TestingWorkspace $workspace,private TestingTarget $target){}
 public function directory():string{return $this->workspace->directory('snapshots');}
 public function create(User $user,string $name,bool $automatic=false):Snapshot {
  $state=$this->target->state();$filename=(string)Str::uuid().'.sqlite';$path=$this->directory().DIRECTORY_SEPARATOR.$filename;$file=fopen($path,'x');if(!$file)throw new \RuntimeException('File snapshot baru tidak dapat dibuat.');fclose($file);
  $source=$this->target->database();$destination=new SQLite3($path,SQLITE3_OPEN_READWRITE);$destination->enableExceptions(true);
  try{if(!$source->backup($destination))throw new \RuntimeException('Backup SQLite gagal.');}finally{$destination->close();$source->close();}
  $copy=$this->target->state($path);if($copy['data_hash']!==$state['data_hash']||$copy['schema_hash']!==$state['schema_hash'])throw new \RuntimeException('Snapshot belum lolos verifikasi.');
  return Snapshot::create(['user_id'=>$user->id,'name'=>$name,'filename'=>$filename,'sha256'=>hash_file('sha256',$path),'bytes'=>filesize($path),'state'=>$copy,'automatic'=>$automatic]);
 }
 public function verifiedPath(Snapshot $snapshot):string {
  if(!preg_match('/^[a-f0-9-]{36}\.sqlite$/',$snapshot->filename))$this->target->fail('Nama snapshot tidak valid.');
  $path=$this->directory().DIRECTORY_SEPARATOR.$snapshot->filename;
  if(is_link($path)||!is_file($path)||realpath($path)!==$path||!hash_equals($snapshot->sha256,hash_file('sha256',$path)))$this->target->fail('File snapshot hilang, berubah, atau di luar direktori terdaftar.');
  $state=$this->target->state($path);if($state['data_hash']!==$snapshot->state['data_hash']||$state['schema_hash']!==$snapshot->state['schema_hash'])$this->target->fail('Isi snapshot tidak sesuai metadata.');return $path;
 }
}

<?php
namespace App\Services;
use Illuminate\Validation\ValidationException;
use SQLite3;
class TestingTarget {
 public const ID='local-test';
 public function __construct(private TestingWorkspace $workspace,private TargetSchema $schema){}
 public function directory():string{return $this->workspace->directory('registered-target');}
 public function path():string{return $this->directory().DIRECTORY_SEPARATOR.'target.sqlite';}
 public function marker():string{return hash_hmac('sha256',self::ID.'|'.$this->path(),config('app.key'));}
 public function fail(string $message):never{throw ValidationException::withMessages(['target'=>$message]);}
 public function environment(string $id):void {
  abort_unless(app()->environment(['local','testing']),403,'Operasi database testing tidak tersedia pada environment produksi.');abort_unless($id===self::ID,404,'Target tidak terdaftar.');
 }
 public function database(string $id=self::ID):SQLite3 {
  $this->environment($id);$path=$this->path();
  $default=config('database.default');$main=config('database.connections.'.$default.'.database');
  if(is_string($main)&&realpath($main)&&realpath($main)===$path)$this->fail('Target tidak boleh sama dengan database aplikasi.');
  if(is_link($path))$this->fail('Symlink database ditolak.');
  if(!file_exists($path)){
   $handle=fopen($path,'x');if(!$handle)$this->fail('Target testing baru tidak dapat dibuat.');fclose($handle);
   $db=new SQLite3($path,SQLITE3_OPEN_READWRITE);$db->enableExceptions(true);$db->exec('PRAGMA foreign_keys=ON');
   $db->exec('CREATE TABLE _testing_guard (id TEXT PRIMARY KEY, marker TEXT NOT NULL)');$statement=$db->prepare('INSERT INTO _testing_guard(id,marker) VALUES(:id,:marker)');$statement->bindValue(':id',self::ID,SQLITE3_TEXT);$statement->bindValue(':marker',$this->marker(),SQLITE3_TEXT);$statement->execute();$this->schema->create($db);$db->close();
  }
  if(realpath($path)!==$path)$this->fail('Lokasi target testing tidak sesuai registry.');
  try{$db=new SQLite3($path,SQLITE3_OPEN_READWRITE);$db->enableExceptions(true);$db->busyTimeout(3000);$db->exec('PRAGMA foreign_keys=ON');$this->assertRegistered($db);return $db;}
  catch(\Throwable $e){if(isset($db))$db->close();$this->fail('Database tidak memiliki penanda dan schema testing yang sesuai; operasi ditolak.');}
 }
 public function assertRegistered(SQLite3 $db):void {
  $marker=$db->querySingle("SELECT marker FROM _testing_guard WHERE id='local-test'");
  if(!is_string($marker)||!hash_equals($this->marker(),$marker))$this->fail('Penanda database testing tidak cocok.');
  $tables=[];$query=$db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");while($row=$query->fetchArray(SQLITE3_ASSOC))$tables[]=$row['name'];
  if($tables!==['_testing_guard','test_accounts','test_events'])$this->fail('Schema target berada di luar registry aplikasi ini.');
 }
 public function state(?string $snapshotPath=null):array {
  if($snapshotPath===null)$db=$this->database();else{$db=new SQLite3($snapshotPath,SQLITE3_OPEN_READONLY);$db->enableExceptions(true);$this->assertRegistered($db);}
  try {
   if($db->querySingle('PRAGMA integrity_check')!=='ok')$this->fail('Pemeriksaan integritas SQLite gagal.');
   $foreign=$db->query('PRAGMA foreign_key_check');if($foreign->fetchArray(SQLITE3_ASSOC))$this->fail('Pemeriksaan foreign key gagal.');
   $rows=[];$counts=[];$schemas=[];
   foreach(['_testing_guard','test_accounts','test_events'] as $table){$result=$db->query('SELECT * FROM "'.$table.'" ORDER BY id');$values=[];while($row=$result->fetchArray(SQLITE3_ASSOC))$values[]=$row;$rows[$table]=$values;if($table!=='_testing_guard')$counts[$table]=count($values);$statement=$db->prepare("SELECT sql FROM sqlite_master WHERE type='table' AND name=:name");$statement->bindValue(':name',$table,SQLITE3_TEXT);$schemas[$table]=$statement->execute()->fetchArray(SQLITE3_ASSOC)['sql'];}
   return ['target'=>self::ID,'integrity'=>'ok','counts'=>$counts,'schema_hash'=>hash('sha256',serialize($schemas)),'data_hash'=>hash('sha256',serialize($rows))];
  }finally{$db->close();}
 }
}

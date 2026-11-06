<?php
namespace Database\Seeders;
use SQLite3;
class TestingDataSeeder {
 public function run(SQLite3 $db):void {
  $db->exec('BEGIN IMMEDIATE');
  try {
   $statement=$db->prepare('INSERT OR IGNORE INTO test_accounts(id,name,email,created_at) VALUES (:id,:name,:email,:created)');
   foreach([1,2] as $id){$statement->bindValue(':id',$id,SQLITE3_INTEGER);$statement->bindValue(':name','Akun Uji '.$id,SQLITE3_TEXT);$statement->bindValue(':email','account-'.$id.'@example.test',SQLITE3_TEXT);$statement->bindValue(':created',now()->toIso8601String(),SQLITE3_TEXT);$statement->execute();$statement->reset();}
   $db->exec("INSERT OR IGNORE INTO test_events(id,account_id,event) VALUES(1,1,'seeded-testing-data')");$db->exec('COMMIT');
  }catch(\Throwable $e){$db->exec('ROLLBACK');throw $e;}
 }
}

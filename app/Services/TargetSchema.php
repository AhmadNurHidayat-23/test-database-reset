<?php
namespace App\Services;
class TargetSchema {
 public function create(\SQLite3 $db):void {
  $db->exec('CREATE TABLE test_accounts (id INTEGER PRIMARY KEY, name TEXT NOT NULL, email TEXT NOT NULL UNIQUE, created_at TEXT NOT NULL)');
  $db->exec('CREATE TABLE test_events (id INTEGER PRIMARY KEY, account_id INTEGER NOT NULL REFERENCES test_accounts(id), event TEXT NOT NULL)');
 }
 public function reset(\SQLite3 $db):void {
  $db->exec('BEGIN IMMEDIATE');
  try{$db->exec('DROP TABLE test_events');$db->exec('DROP TABLE test_accounts');$this->create($db);$db->exec('COMMIT');}
  catch(\Throwable $e){$db->exec('ROLLBACK');throw $e;}
 }
}

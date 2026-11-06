<?php
namespace App\Services;
use RuntimeException;
class TestingWorkspace {
 public function directory(string $suffix):string {
  $expected=realpath(base_path()).DIRECTORY_SEPARATOR.'storage';$storage=realpath(storage_path());
  if(!$storage||($storage!==$expected&&!str_starts_with($storage,$expected.DIRECTORY_SEPARATOR)))throw new RuntimeException('Storage testing harus berada di dalam proyek aplikasi ini.');
  $current=$storage;
  foreach(explode('/','app/testing/'.$suffix) as $part){if(!preg_match('/^[A-Za-z0-9_-]+$/',$part))throw new RuntimeException('Direktori tidak valid.');$current.=DIRECTORY_SEPARATOR.$part;if(is_link($current))throw new RuntimeException('Symlink tidak diizinkan untuk database testing.');if(!is_dir($current)&&!mkdir($current,0700))throw new RuntimeException('Direktori testing tidak dapat dibuat.');if(!str_starts_with(realpath($current),$storage.DIRECTORY_SEPARATOR))throw new RuntimeException('Direktori testing keluar dari storage.');}
  return realpath($current);
 }
}

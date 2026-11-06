<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Snapshot extends Model {protected $guarded=[];protected function casts():array{return ['state'=>'array','automatic'=>'boolean'];}}

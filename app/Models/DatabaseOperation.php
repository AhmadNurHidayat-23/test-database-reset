<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DatabaseOperation extends Model {protected $guarded=[];protected function casts():array{return ['details'=>'array'];}}

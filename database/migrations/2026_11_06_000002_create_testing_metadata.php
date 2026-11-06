<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('snapshots',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained();$t->string('name',100);$t->string('filename')->unique();$t->string('sha256',64);$t->unsignedInteger('bytes');$t->json('state');$t->boolean('automatic')->default(false);$t->timestamps();});
  Schema::create('database_operations',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained();$t->string('kind');$t->string('status');$t->json('details');$t->timestamps();});
 }
 public function down():void{Schema::dropIfExists('database_operations');Schema::dropIfExists('snapshots');}
};

<?php
namespace Tests\Feature;
use App\Models\{User,Snapshot,DatabaseOperation};
use App\Services\{TestingTarget,SnapshotStore};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
class TestingDatabaseTest extends TestCase {
 use RefreshDatabase;
 protected function setUp():void{parent::setUp();$root=base_path('storage/framework/testing/reset-'.Str::uuid());if(!is_dir($root))mkdir($root,0700,true);$this->app->useStoragePath($root);}
 private function owner():User{$user=User::factory()->create();$user->forceFill(['is_admin'=>true])->save();return $user;}
 public function test_registration_only_allows_initial_owner():void{$this->post('/register',['name'=>'Pemilik','email'=>'owner@example.test','password'=>'Testing-owner-123!','password_confirmation'=>'Testing-owner-123!'])->assertRedirect('/');$this->assertTrue((bool)User::first()->is_admin);$this->post('/logout');$this->get('/register')->assertForbidden();$this->post('/register',['name'=>'Other','email'=>'other@example.test','password'=>'Testing-owner-123!','password_confirmation'=>'Testing-owner-123!'])->assertForbidden();}
 public function test_guest_and_non_owner_are_rejected():void{$this->get('/')->assertRedirect('/login');$this->actingAs(User::factory()->create())->get('/')->assertForbidden();$this->post('/snapshots',['name'=>'not allowed'])->assertForbidden();}
 public function test_snapshot_reset_and_restore_verify_actual_data_and_preserve_metadata():void{
  $owner=$this->owner();$this->actingAs($owner)->post('/targets/local-test/seed')->assertRedirect();$target=app(TestingTarget::class);$before=$target->state();$this->assertSame(2,$before['counts']['test_accounts']);
  $this->post('/snapshots',['name'=>'Sebelum reset'])->assertRedirect();$snapshot=Snapshot::firstOrFail();$this->assertGreaterThan(0,$snapshot->bytes);
  $this->post('/targets/local-test/reset',['confirmation'=>'RESET local-test'])->assertRedirect();$this->assertSame(0,array_sum($target->state()['counts']));$this->assertDatabaseHas('users',['id'=>$owner->id]);$this->assertDatabaseCount('snapshots',2);
  $this->post('/targets/local-test/restore/'.$snapshot->id,['confirmation'=>'PULIHKAN '.$snapshot->id])->assertRedirect();$after=$target->state();$this->assertSame($before['data_hash'],$after['data_hash']);$this->assertSame($before['schema_hash'],$after['schema_hash']);$this->assertTrue(DatabaseOperation::where('kind','restore')->first()->details['verified']);$this->get('/')->assertInertia(fn($page)=>$page->where('target.id','local-test')->where('target.state.counts.test_accounts',2));
 }
 public function test_reset_and_restore_require_confirmation_before_any_destructive_work():void{
  $this->actingAs($this->owner())->post('/targets/local-test/seed');$target=app(TestingTarget::class);$before=$target->state()['data_hash'];$this->post('/snapshots',['name'=>'Snapshot']);$snapshot=Snapshot::first();
  $this->post('/targets/local-test/reset')->assertSessionHasErrors('confirmation');$this->post('/targets/local-test/reset',['confirmation'=>'RESET production'])->assertSessionHasErrors('target');$this->post('/targets/local-test/restore/'.$snapshot->id)->assertSessionHasErrors('confirmation');$this->post('/targets/local-test/restore/'.$snapshot->id,['confirmation'=>'PULIHKAN 999'])->assertSessionHasErrors('target');$this->assertSame($before,$target->state()['data_hash']);$this->assertDatabaseCount('snapshots',1);
 }
 public function test_tampered_snapshot_is_rejected_without_changing_target():void{
  $this->actingAs($this->owner())->post('/targets/local-test/seed');$this->post('/snapshots',['name'=>'Verified']);$snapshot=Snapshot::first();$target=app(TestingTarget::class);$before=$target->state()['data_hash'];$path=app(SnapshotStore::class)->directory().DIRECTORY_SEPARATOR.$snapshot->filename;file_put_contents($path,'tampered',FILE_APPEND);
  $this->post('/targets/local-test/restore/'.$snapshot->id,['confirmation'=>'PULIHKAN '.$snapshot->id])->assertSessionHasErrors('target');$this->assertSame($before,$target->state()['data_hash']);$this->assertSame('failed',DatabaseOperation::where('kind','restore')->first()->status);
 }
 public function test_wrong_marker_blocks_reset():void{
  $this->actingAs($this->owner())->post('/targets/local-test/seed');$db=app(TestingTarget::class)->database();$db->exec("UPDATE _testing_guard SET marker='not-registered'");$db->close();
  $this->post('/targets/local-test/reset',['confirmation'=>'RESET local-test'])->assertSessionHasErrors('target');$this->assertSame(0,DatabaseOperation::where('kind','reset')->count());
 }
 public function test_unknown_targets_free_sql_and_production_are_blocked():void{
  $this->actingAs($this->owner())->post('/targets/production/reset',['confirmation'=>'RESET local-test'])->assertNotFound();$this->post('/targets/local-test/reset',['confirmation'=>'RESET local-test','path'=>'C:/other/database.sqlite','sql'=>'DROP TABLE users'])->assertSessionHasErrors(['path','sql']);$this->app['env']='production';$this->get('/')->assertForbidden();
 }
 public function test_seeder_is_idempotent():void{$this->actingAs($this->owner())->post('/targets/local-test/seed');$before=app(TestingTarget::class)->state();$this->post('/targets/local-test/seed')->assertRedirect();$after=app(TestingTarget::class)->state();$this->assertSame($before['data_hash'],$after['data_hash']);$this->assertSame(['test_accounts'=>2,'test_events'=>1],$after['counts']);}
 public function test_other_owner_cannot_restore_another_users_snapshot():void{$a=$this->owner();$this->actingAs($a)->post('/snapshots',['name'=>'Private']);$snapshot=Snapshot::first();$this->actingAs($this->owner())->post('/targets/local-test/restore/'.$snapshot->id,['confirmation'=>'PULIHKAN '.$snapshot->id])->assertForbidden();}
 public function test_application_database_cannot_be_used_as_testing_target():void{$this->actingAs($this->owner());$target=app(TestingTarget::class);$db=$target->database();$db->close();$original=config('database.connections.sqlite.database');config(['database.connections.sqlite.database'=>$target->path()]);$this->post('/targets/local-test/reset',['confirmation'=>'RESET local-test'])->assertSessionHasErrors('target');config(['database.connections.sqlite.database'=>$original]);}
}

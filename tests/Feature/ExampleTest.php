<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
class ExampleTest extends TestCase {use RefreshDatabase;public function test_login_screen_is_available():void{$this->get('/login')->assertOk();}}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Http\Controllers\UserController;
use App\Models\ApiConnect;
Use App\Models\User;
use Tests\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Auth;
use Mockery;


class UserController_Test extends TestCase
{
    public function setUp() : void {
        parent::setUp();

        $dataUser = new User();
        $dataUser->name = 'admin';
        Auth::shouldReceive('check')->andreturn(true);
        Auth::shouldReceive('user')->andreturn($dataUser);
    }

    // public function authSalesforce_Test_FirstInsert() {
    //     $mockExternalApi = new MockHandler([
    //             new Response(200 , ['Content-type' => 'application/json'], '{"access_token":"3K6zNUxzWu","refresh_token":"Y5TcR0gFUD"}')
    //     ]);
    //     $handlerStack = HandlerStack::create($mockExternalApi);
    //     $mock = Mockery::mock(UserController::class);
    //     $this->app->instance(UserController::class, $mock)
    //     $reponse = $this->call->
    //     $apiToken = ApiConnect::find(1);
    //     $this->assertNotNull($apiToken);
    // }

    // public function authSalesforce_Test_IsInserted() {
    //     ApiConnect::create([
    //         'accessToken' => '3K6zNUxzWu',
    //         'refreshToken' => 'Y5TcR0gFUD',
    //         'status' => __('messages.Synced')
    //     ]);
    //     $mock->shouldReceive('show')->with([
    //         'user' => Auth::user()->id
    //     ])->once();
    //     $apiToken = ApiConnect::find(1);
    //     $this->assertNotNull($apiToken);
    // }
}

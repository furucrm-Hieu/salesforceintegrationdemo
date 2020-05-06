<?php

namespace Tests\Unit;

use app\Helpers\HelperAuthenticateSalesforce;
use App\Http\Controllers\ApiController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Http\Controllers\UserController;
use App\Models\ApiConnect;
Use App\Models\User;
use GuzzleHttp\Client;
use Tests\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery;


class UserController_Test extends TestCase
{
    use RefreshDatabase;

    public function setUp() : void {
        parent::setUp();

        $dataUser = new User();
        $dataUser->name = 'admin';
        Auth::shouldReceive('check')->andreturn(true);
        Auth::shouldReceive('user')->andreturn($dataUser);
    }

    public function tearDown(): void {
        parent::tearDown();
        Mockery::close();
    }

    public function testAuthSalesforce_FirstInsert() {

        // $mock = $this->mock(ApiController::class);
        // $mock->shouldReceive('callback')->with(['code' => '3K6zNUxzWu'])
        //         ->once();
        // //$reponse = $this->post('/callback', [ 'request' => $mock]);

        // $this->assertDatabaseHas('api_connect', [
        //     'accessToken' => '3K6zNUxzWu',
        //     'refreshToken' => 'Y5TcR0gFUD',
        //     'status' => 'Synced'
        // ]);
        $mock = new MockHandler([
            new Response(200, [], '{"token_access":"3K6zNUxzWu", "refresh_token":"Y5TcR0gFUD"}'),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $reponse = $client->request('GET', '/callback', [
            'code' => '3K6zNUxzWu'
        ]);

        $this->assertDatabaseHas('api_connect', [
            'accessToken' => '3K6zNUxzWu',
            'refreshToken' => 'Y5TcR0gFUD',
            'status' => 'Synced'
        ]);
    }

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

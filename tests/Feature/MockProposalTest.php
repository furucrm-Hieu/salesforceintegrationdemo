<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Contracts\Http\Kernel;
use Mockery;
Use App\Models\User;
use App\Models\Proposal;
use Exception;

class MockProposalTest extends TestCase
{
    // use WithoutMiddleware;
    use DatabaseTransactions;

    public function setUp(): void {
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

    public function testProposalIndexTrue()
    {
        $dataProposal = $this->factoryProposal(1);

        $proposal = Mockery::mock(Proposal::class);
        $proposal->shouldReceive('whereNotNull->get')->once()->andReturn($dataProposal);
        $this->app->instance(Proposal::class, $proposal);
        
        $response = $this->call('GET', '/proposal');
    
        $response->assertStatus(200);
        $response->assertViewHas('proposals');

        $listProposalOnView = $response->original['proposals'];
        $this->assertEquals(1, count($listProposalOnView));
    }

    public function testProposalIndexFalse()
    {
        $dataProposal = $this->factoryProposal(2);

        $proposal = Mockery::mock(Proposal::class);
        $proposal->shouldReceive('whereNotNull->get')->once()->andReturn($dataProposal);
        $this->app->instance(Proposal::class, $proposal);
        
        $response = $this->call('GET', '/proposal');
    
        $response->assertStatus(200);
        $response->assertViewHas('proposals');

        $listProposalOnView = $response->original['proposals'];
        $this->assertNotEquals(1, count($listProposalOnView));
    }

    public function testProposalIndexNull()
    {
        $proposal = Mockery::mock(Proposal::class);
        $proposal->shouldReceive('whereNotNull->get')->once()->andReturn([]);
        $this->app->instance(Proposal::class, $proposal);
        
        $response = $this->call('GET', '/proposal');
    
        $response->assertStatus(200);
        $response->assertViewHas('proposals');

        $listProposalOnView = $response->original['proposals'];
        $this->assertEquals(0, count($listProposalOnView));
    }

    public function testProposalStoreTrue()
    {
        $this->withoutMiddleware();

        $input = [
            'name' => 'Unit test',
            'approved_at' => '2020-04-20 15:00:00',
            'proposed_at' => '2020-04-20 15:00:00',
            'year' => '2020',
        ];

        $proposal = Mockery::mock(Proposal::class);
        $proposal->shouldReceive('create')->andReturn(new Proposal(['id' => 1]));
        $this->app->instance(Proposal::class, $proposal);  
        
        $response = $this->call('POST', '/proposal', $input);

        $response->assertRedirect('/proposal/1');
    }

    public function testProposalStoreFalseValidate()
    {
        $this->withoutMiddleware();

        $input = [
            'name' => 'Unit test',
            'approved_at' => '2020-04-20 15:00:00',
            'proposed_at' => '2020-04-20 15:00:00',
            'year' => '202021',
        ];
        
        $response = $this->call('POST', '/proposal', $input);
        $response->assertRedirect('/proposal/create');
    }

    // public function testProposalStoreFalseTryCatch()
    // {
    //     $this->withoutMiddleware();

    //     $input = [
    //         'name' => 'Unit test',
    //         'approved_at' => '2020-04-20 15:00:00',
    //         'proposed_at' => '2020-04-20 15:00:00',
    //         'year' => '2020',
    //     ];

    //     $proposal = Mockery::mock(Proposal::class);
    //     $proposal->shouldReceive('create')->andThrow(\Exception::class);
    //     $this->app->instance(Proposal::class, $proposal);  
        
    //     $response = $this->call('POST', '/proposal', $input);
    //     dd($response);
    //     $response->assertRedirect('/proposal/create');
    // }

    public function testProposalShowTrue()
    {
        $dataProposal = $this->factoryProposal(1)[0];

        $proposal = Mockery::mock(Proposal::class);
        $proposal->shouldReceive('findOrFail')->andReturn($dataProposal);
        $this->app->instance(Proposal::class, $proposal);
        
        $response = $this->call('GET', '/proposal/1');
        
        $response->assertStatus(200);
        $response->assertViewHas('proposal');
        $response->assertViewHas('listBudget');

        $listProposalOnView = $response->original['proposal'];
        $this->assertEquals('Unit test', $listProposalOnView->name);
    }

    // public function testProposalShowFalseTryCatch()
    // {
    //     $proposal = Mockery::mock(Proposal::class);
    //     $proposal->shouldReceive('findOrFail')->andThrow(\Exception::class);
    //     $this->app->instance(Proposal::class, $proposal);
        
    //     $response = $this->call('GET', '/proposal/1');
        
    //     $response->assertRedirect('/proposal');
    // }

    public function testProposalEditTrue()
    {
        $dataProposal = $this->factoryProposal(1)[0];

        $proposal = Mockery::mock(Proposal::class);
        $proposal->shouldReceive('findOrFail')->andReturn($dataProposal);
        $this->app->instance(Proposal::class, $proposal);
        
        $response = $this->call('GET', '/proposal/1/edit');

        $response->assertStatus(200);
        $response->assertViewHas('proposal');

        $listProposalOnView = $response->original['proposal'];
        $this->assertEquals('Unit test', $listProposalOnView->name);
    }

    // public function testProposalEditFalseTryCatch()
    // {
    //     $proposal = Mockery::mock(Proposal::class);
    //     $proposal->shouldReceive('findOrFail')->andThrow(\Exception::class);
    //     $this->app->instance(Proposal::class, $proposal);
        
    //     $response = $this->call('GET', '/proposal/1/edit');
        
    //     $response->assertRedirect('/proposal');
    // }

    public function testProposalUpdateTrue()
    {
        $this->withoutMiddleware();

        $input = [
            'name' => 'Unit test11',
            'approved_at' => '2020-04-20 15:00:00',
            'proposed_at' => '2020-04-20 15:00:00',
            'year' => '2020',
        ];

        $proposal = Mockery::mock(Proposal::class);
        $proposal->shouldReceive('findOrFail')->andReturn(new Proposal(['id' => 1]))
            ->shouldReceive('update')->andReturn(true);
        $this->app->instance(Proposal::class, $proposal);  
        
        $response = $this->call('PUT', '/proposal/1', $input);

        $response->assertRedirect('/proposal/1');
    }

    // public function testProposalUpdateFalseValidate()
    // {
    //     $this->withoutMiddleware();

    //     $input = [
    //         'name' => 'Unit test',
    //         'approved_at' => '2020-04-20 15:00:00',
    //         'proposed_at' => '2020-04-20 15:00:00',
    //         'year' => '202020',
    //     ];
        
    //     $response = $this->call('PUT', '/proposal/1', $input);
    //     dd($response);
    //     $response->assertRedirect('/proposal/1/edit');
    // }

    // public function testProposalUpdateFalseTryCatch()
    // {
    //     $this->withoutMiddleware();

    //     $input = [
    //         'name' => 'Unit test',
    //         'approved_at' => '2020-04-20 15:00:00',
    //         'proposed_at' => '2020-04-20 15:00:00',
    //         'year' => '2020',
    //     ];

    //     $proposal = Mockery::mock(Proposal::class);
    //     $proposal->shouldReceive('findOrFail')->andThrow(\Exception::class);
    //     $this->app->instance(Proposal::class, $proposal);  
        
    //     $response = $this->call('PUT', '/proposal/1', $input);
    //     $response->assertRedirect('/proposal/1/edit');
    // }

    public function factoryProposal($number = 1) {
        $proposals = [];

        for ($i = 1; $i <= $number; $i++) {
            $proposals[] = new Proposal([
                'id' => $i,
                'name' => 'Unit test',
                'proposed_at__c' => '2020-04-20 15:00:00',
                'approved_at__c' => '2020-04-20 15:00:00',
                'year__c' => '2020',
                'total_amount__c' => '0',
                'details__c' => 'unittest',
                'external_id__c' => 'unittest',
                'sfid' => 'unittest',
            ]);
        }
        
        return $proposals;
    }
}

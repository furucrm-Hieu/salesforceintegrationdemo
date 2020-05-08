<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proposal;
use App\Models\Budget;
use App\Models\ProposalBudget;
use App\Models\ApiConnect;
use App\Helpers\HelperHandleTotalAmount;
use App\Helpers\HelperGuzzleService;
use DB, Session;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProposalBudgetController extends Controller
{
    protected $mProposal;
    protected $mBudget;
    protected $mProposalBudget;
    protected $hHelperHandleTotalAmount;
    protected $hHelperGuzzleService;

    public function __construct(Proposal $mProposal, ProposalBudget $mProposalBudget, Budget $mBudget, HelperHandleTotalAmount $hHelperHandleTotalAmount, HelperGuzzleService $hHelperGuzzleService) {
        $this->mProposal = $mProposal;
        $this->mBudget = $mBudget;
        $this->mProposalBudget = $mProposalBudget;
        $this->hHelperHandleTotalAmount = $hHelperHandleTotalAmount;
        $this->hHelperGuzzleService = $hHelperGuzzleService;
        $this->vApiConnect = ApiConnect::latest()->first();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //return view('proposal.list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {   
        return view('proposal_budget/create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {

            $validator = Validator::make($request->all(), $this->validation());

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            $requestData = [];
            $requestData['budget__c'] = $request->input('budget__c');
            $requestData['proposal__c'] = $request->input('proposal__c');
            $requestData['amount__c'] = $request->input('amount');

            $proposalBudget = $this->mProposalBudget->create($requestData);

            DB::commit();

            $this->hHelperHandleTotalAmount->caseCreateDeleteJunction($proposalBudget->proposal__c, $proposalBudget->budget__c);

            if($this->vApiConnect && $this->vApiConnect->expried == false) {

                $dataProBud = [];
                $dataProBud['Proposal__c'] = $proposalBudget->proposal__c;
                $dataProBud['Budget__c'] = $proposalBudget->budget__c;
                $dataProBud['Amount__c'] = $proposalBudget->amount__c;

                $response = $this->hHelperGuzzleService::guzzlePost(config('authenticate.api_uri').'/Proposal_Budget__c/', $this->vApiConnect->accessToken, $dataProBud);
                $response = json_decode($response);
                
                if(isset($response->success) && $response->success == true) {
                    $proposalBudget->update(['sfid' => $response->id]);
                }else if(isset($response->success) && $response->success == false) {
                    $resFreshToken = $this->hHelperGuzzleService::refreshToken($this->vApiConnect->refreshToken);
                    if(json_decode($resFreshToken)->success == true){
                        $access_token = json_decode($resFreshToken)->access_token;

                        $response1 = $this->hHelperGuzzleService::guzzlePost(config('authenticate.api_uri').'/Proposal_Budget__c/', $access_token, $dataProBud);
                        $response1 = json_decode($response1);
                        if(isset($response1->success) && $response1->success == true) {
                            $proposalBudget->update(['sfid' => $response1->id]);
                        }
                    }
                }
            }

            
            if($request->input('typeRedirect') == 'budget') {
                $budgetRedirect = $this->mBudget::where('sfid', $request->input('budget__c'))->firstOrFail();
                return redirect('budget/'.$budgetRedirect->id);
            }
            else {
                $proposalRedirect = $this->mProposal::where('sfid', $request->input('proposal__c'))->firstOrFail();
                return redirect('proposal/'.$proposalRedirect->id);
            }
                        
        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Store - ProposalBudgetController');
            DB::rollback();
            return redirect()->back()->withErrors(['message' => __('messages.System_Error')])->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $proposalBudget = new $this->mProposalBudget([
                'proposal__c' => '',
                'budget__c' => '',
                'amount__c' => ''
            ]);

            $dataCheckType = explode("-", $id);
            if($dataCheckType[0] == 'proposal') {
                $proposalBudget->proposal__c = $this->mProposal::findOrFail($dataCheckType[1])->sfid;
            }elseif ($dataCheckType[0]  == 'budget') {
                $proposalBudget->budget__c = $this->mBudget->findOrFail($dataCheckType[1])->sfid;
            }
            else {
                abort(404);
            }

            $typeRedirect = $dataCheckType[0];
            $linkRedirect = url($typeRedirect.'/'.$dataCheckType[1]);

            $proposals = $this->mProposal::whereNotNull('sfid')->orderBy('name')->get()->pluck('name','sfid');
            $budgets = $this->mBudget::whereNotNull('sfid')->orderBy('name')->get()->pluck('name','sfid');

            return view('proposal_budget.create', compact('proposalBudget', 'proposals', 'budgets', 'typeRedirect', 'linkRedirect'));
        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Show - ProposalBudgetController');
            abort(404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {

            $dataCheckType = explode("-", $id);
            if($dataCheckType[0] == 'proposal' || $dataCheckType[0] == 'budget') {
                $typeRedirect = $dataCheckType[0];
            }
            else {
                abort(404);
            }

            $proposalBudget = $this->mProposalBudget::findOrFail($dataCheckType[1]);
            $proposals = $this->mProposal::whereNotNull('sfid')->orderBy('name')->get()->pluck('name','sfid');
            $budgets = $this->mBudget::whereNotNull('sfid')->orderBy('name')->get()->pluck('name','sfid');

            return view('proposal_budget.edit', compact('proposalBudget', 'proposals', 'budgets', 'typeRedirect'));
        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Edit - ProposalController');
            abort(404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {

            $validator = Validator::make($request->all(), $this->validation());

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $proposalBudget = $this->mProposalBudget::findOrFail($id);

            $requestData = [];
            $requestData['proposal__c'] = $request->input('proposal__c');
            $requestData['budget__c'] = $request->input('budget__c');
            $requestData['amount__c'] = $request->input('amount');

            $proposalBudget->update($requestData);

            DB::commit();

            $this->hHelperHandleTotalAmount->caseDeleteParentOrJunction('all');

            if($this->vApiConnect && $this->vApiConnect->expried == false) {

                $dataProBud = [];
                $dataProBud['Proposal__c'] = $proposalBudget->proposal__c;
                $dataProBud['Budget__c'] = $proposalBudget->budget__c;
                $dataProBud['Amount__c'] = $proposalBudget->amount__c;

                $response = $this->hHelperGuzzleService::guzzleUpdate(config('authenticate.api_uri').'/Proposal_Budget__c/'.$proposalBudget->sfid, $this->vApiConnect->accessToken, $dataProBud);
                $response = json_decode($response);

                if(isset($response->success) && $response->success == false) {
                    $resFreshToken = $this->hHelperGuzzleService::refreshToken($this->vApiConnect->refreshToken);

                    if(json_decode($resFreshToken)->success == true){
                        $access_token = json_decode($resFreshToken)->access_token;
                        $response1 = $this->hHelperGuzzleService::guzzleUpdate(config('authenticate.api_uri').'/Proposal_Budget__c/'.$proposalBudget->sfid, $access_token, $dataProBud);
                    }
                }
            }

            if($request->input('typeRedirect') == 'budget') {
                $budgetRedirect = $this->mBudget::where('sfid', $request->input('budget__c'))->firstOrFail();
                return redirect('budget/'.$budgetRedirect->id);
            }
            else {
                $proposalRedirect = $this->mProposal::where('sfid', $request->input('proposal__c'))->firstOrFail();
                return redirect('proposal/'.$proposalRedirect->id);
            }

        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Update - ProposalBudgetController');
            DB::rollback();
            return redirect()->back()->withErrors(['message' => __('messages.System_Error')])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try{
            $proposalBudget = $this->mProposalBudget::findOrFail($id);
            $proposalBudget->delete();
            DB::commit();

            $this->hHelperHandleTotalAmount->caseCreateDeleteJunction($proposalBudget->proposal__c, $proposalBudget->budget__c);

            if($this->vApiConnect && $this->vApiConnect->expried == false) {
                $response = $this->hHelperGuzzleService::guzzleDelete(config('authenticate.api_uri').'/Proposal_Budget__c/'.$proposalBudget->sfid, $this->vApiConnect->accessToken);

                $response = json_decode($response);
                if(isset($response->success) && $response->success == false) {
                    $resFreshToken = $this->hHelperGuzzleService::refreshToken($this->vApiConnect->refreshToken);
                    
                    if(json_decode($resFreshToken)->success == true){
                        $access_token = json_decode($resFreshToken)->access_token;
                        $response1 = $this->hHelperGuzzleService::guzzleDelete(config('authenticate.api_uri').'/Proposal_Budget__c/'.$proposalBudget->sfid, $access_token);
                    }
                }
            }

            return response()->json(['success' => true]);
        }catch(\Exception $ex) {
            Log::info($ex->getMessage(). ' Destroy - ProposalBudgetController');
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    private function validation() {
        return [
            'budget__c' => 'required',
            'proposal__c' => 'required',
            'amount' => 'max:12',
        ];
    }
}

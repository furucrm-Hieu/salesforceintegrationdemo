<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proposal;
use App\Models\Budget;
use App\Models\ProposalBudget;
use Illuminate\Support\Facades\Auth;
use App\Helpers\HelperHandleTotalAmount;
use App\Helpers\HelperGuzzleService;
use App\Helpers\HelperConvertDateTime;
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
    protected $hHelperConvertDateTime;
    protected $hHelperHandleTotalAmount;
    protected $hHelperGuzzleService;

    public function __construct(Proposal $mProposal, ProposalBudget $mProposalBudget, Budget $mBudget, HelperHandleTotalAmount $hHelperHandleTotalAmount, HelperGuzzleService $hHelperGuzzleService, HelperConvertDateTime $hHelperConvertDateTime) {
        $this->mProposal = $mProposal;
        $this->mBudget = $mBudget;
        $this->mProposalBudget = $mProposalBudget;
        $this->hHelperHandleTotalAmount = $hHelperHandleTotalAmount;
        $this->hHelperGuzzleService = $hHelperGuzzleService;
        $this->hHelperConvertDateTime = $hHelperConvertDateTime;
    }


    public function createJunction($id) {

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

            $apiConnect = Auth::user()->accessToken;
            $proposals = $this->mProposal->orderBy('name')->get()->pluck('name','sfid');
            $budgets = $this->mBudget->orderBy('name')->get()->pluck('name','sfid');
            $linkRedirect  = url($dataCheckType[0].'/'.$dataCheckType[1]);
            $type = 'create';

            return view('proposal_budget.create', compact('proposalBudget', 'proposals', 'budgets', 'linkRedirect', 'apiConnect', 'type'));
        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- createJunction - ProposalBudgetController');
            abort(404);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Check validator
            $validator = Validator::make($request->all(), $this->validation());
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Flag sfid check insert salesforce true or false.
            $sfid = '';

            // Check and insert to salesforce.
            if(!empty(Auth::user()->accessToken)) {

                $dataProBud = [];
                $dataProBud['Budget__c'] = $request->input('budget__c');
                $dataProBud['Proposal__c'] = $request->input('proposal__c');
                $dataProBud['Amount__c'] = $request->input('amount');
                
                $response = $this->hHelperGuzzleService::guzzlePost(config('authenticate.api_uri').'/Proposal_Budget__c/', Auth::user()->accessToken, $dataProBud);
                
                // if insert sf false and status code 401, call again insert.
                if(isset($response->success) && $response->success == false) {
                    if($response->statusCode == 401) {

                        $resFreshToken = $this->hHelperGuzzleService::refreshToken(Auth::user()->refreshToken);
                        if($resFreshToken->success == true){
                            $access_token = $resFreshToken->access_token;

                            $response1 = $this->hHelperGuzzleService::guzzlePost(config('authenticate.api_uri').'/Proposal_Budget__c/', $access_token, $dataProBud);

                            if(isset($response1->success) && $response1->success == true) {
                                // set sf id
                                $sfid = $response1->id;
                            }
                        }    
                    }

                    if($response->statusCode == 400) {
                        return redirect()->back()->withErrors(['message' => $response->message])->withInput();
                    }
                    
                } else if (isset($response->success) && $response->success == true) {
                    // set sf id
                    $sfid = $response->id;
                }
            }

            // Check if sfid till empty, return false.
            if(empty($sfid)) {
                return redirect()->back()->withErrors(['message' => __('messages.Token_Error')])->withInput();
            }

            DB::beginTransaction();
            $requestData = [];
            $requestData['budget__c'] = $request->input('budget__c');
            $requestData['proposal__c'] = $request->input('proposal__c');
            $requestData['amount__c'] = $request->input('amount');
            $requestData['sfid'] = $sfid;

            $proposalBudget = $this->mProposalBudget->create($requestData);

            $proposalSfid = $request->input('proposal__c');
            $proposal = $this->mProposal::where('sfid', $proposalSfid)->first();
            if($proposal->status_approve == $this->hHelperConvertDateTime::APPROVED){
                $proposal->type_submit = true;
                $proposal->save();
            }

            DB::commit();

            $this->hHelperHandleTotalAmount->caseCreateDeleteJunction($proposalBudget->proposal__c, '' , $proposalBudget->budget__c);

            return redirect($request->linkRedirect);
                        
        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Store - ProposalBudgetController');
            DB::rollback();
            return redirect()->back()->withErrors(['message' => __('messages.System_Error')])->withInput();
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

            $proposalBudget = $this->mProposalBudget::findOrFail($id);
            $proposal = $this->mProposal::where('sfid', $proposalBudget->proposal__c)->first();
            $linkRedirect = url('proposal/'.$proposal->id);

            if($proposal->status_approve == $this->hHelperConvertDateTime::SUBMIT){
                return redirect($linkRedirect);
            }

            $proposals = $this->mProposal::orderBy('name')->get()->pluck('name', 'sfid');
            $budgets = $this->mBudget::orderBy('name')->get()->pluck('name', 'sfid');
            $apiConnect = Auth::user()->accessToken;   
            $type = 'edit';

            return view('proposal_budget.edit', compact('proposalBudget', 'proposals', 'budgets', 'apiConnect', 'linkRedirect', 'type'));

        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Edit - ProposalBudgetController');
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
        try {
            // Check validator
            $validator = Validator::make($request->all(), $this->validation_edit());
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $proposalBudget = $this->mProposalBudget::findOrFail($id);

            // Flag flagUpdate check update salesforce true or false.
            $flagUpdate = false;

            // Check and update to salesforce.
            if(!empty(Auth::user()->accessToken)) {

                $dataProBud = [];
                $dataProBud['Amount__c'] = $request->input('amount');

                $response = $this->hHelperGuzzleService::guzzleUpdate(config('authenticate.api_uri').'/Proposal_Budget__c/'.$proposalBudget->sfid, Auth::user()->accessToken, $dataProBud);

                if(isset($response->success) && $response->success == false) {
                    if($response->statusCode == 401) {
                        $resFreshToken = $this->hHelperGuzzleService::refreshToken(Auth::user()->refreshToken);

                        if($resFreshToken->success == true){
                            $access_token = $resFreshToken->access_token;

                            $response1 = $this->hHelperGuzzleService::guzzleUpdate(config('authenticate.api_uri').'/Proposal_Budget__c/'.$proposalBudget->sfid, $access_token, $dataProBud);

                            if(isset($response1->success) && $response1->success == true) {
                                $flagUpdate = true;
                            }
                        }
                    }

                    if($response->statusCode == 400) {
                        return redirect()->back()->withErrors(['message' => $response->message])->withInput();
                    }

                } else if (isset($response->success) && $response->success == true) {
                    $flagUpdate = true;
                }
            }

            if(!$flagUpdate) {
                return redirect()->back()->withErrors(['message' => __('messages.Token_Error')])->withInput();
            }

            DB::beginTransaction();

            $requestData = [];
            $requestData['amount__c'] = $request->input('amount');
            $proposalBudget->update($requestData);

            $proposalSfid = $request->input('proposal__c');
            $proposal = $this->mProposal::where('sfid', $proposalSfid)->first();
            if($proposal->status_approve == $this->hHelperConvertDateTime::APPROVED){
                $proposal->type_submit = true;
                $proposal->save();
            }

            DB::commit();
            
            $this->hHelperHandleTotalAmount->caseCreateDeleteJunction($proposalBudget->proposal__c, '', $proposalBudget->budget__c);

            return redirect($request->linkRedirect);

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
    public function destroy(Request $request, $id)
    {
        try{

            $proposalBudget = $this->mProposalBudget::findOrFail($id);

            // Flag flagDelete check delete salesforce true or false.
            $flagDelete = false;

            if(!empty(Auth::user()->accessToken)) {

                $response = $this->hHelperGuzzleService::guzzleDelete(config('authenticate.api_uri').'/Proposal_Budget__c/'.$proposalBudget->sfid, Auth::user()->accessToken);
                
                if(isset($response->success) && $response->success == false) {
                    if($response->statusCode == 401) {
                        $resFreshToken = $this->hHelperGuzzleService::refreshToken(Auth::user()->refreshToken);
    
                        if($resFreshToken->success == true){
                            $access_token = $resFreshToken->access_token;

                            $response1 = $this->hHelperGuzzleService::guzzleDelete(config('authenticate.api_uri').'/Proposal_Budget__c/'.$proposalBudget->sfid, $access_token);

                            if(isset($response1->success) && $response1->success == true) {
                                $flagDelete = true;
                            }
                        }
                    }
                } else if (isset($response->success) && $response->success == true) {
                    $flagDelete = true;
                }  
            }

            if(!$flagDelete) {
                if($request->ajax()){
                    return response()->json(['success' => false]);
                }
            }

            DB::beginTransaction();
            $proposalBudget->delete();

            $proposal = $this->mProposal::where('sfid', $proposalBudget->proposal__c)->first();
            if($proposal->status_approve == $this->hHelperConvertDateTime::APPROVED){
                $proposal->type_submit = true;
                $proposal->save();
            }
            DB::commit();
            
            $this->hHelperHandleTotalAmount->caseCreateDeleteJunction($proposalBudget->proposal__c, '', $proposalBudget->budget__c);

            if($request->ajax()){
                return response()->json(['success' => true]);
            }

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

    private function validation_edit() {
        return [
            'amount' => 'max:12',
        ];
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Budget;
use App\Models\ProposalBudget;
use App\Models\ExpenseBudget;
use App\Models\User;
use App\Helpers\HelperHandleTotalAmount;
use App\Helpers\HelperGuzzleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BudgetController extends Controller
{
    protected $mBudget;
    protected $mProposalBudget;
    protected $mExpenseBudget;
    protected $hHelperHandleTotalAmount;
    protected $hHelperGuzzleService;

    public function __construct(Budget $mBudget, ProposalBudget $mProposalBudget, ExpenseBudget $mExpenseBudget, HelperHandleTotalAmount $hHelperHandleTotalAmount, HelperGuzzleService $hHelperGuzzleService) {
        $this->mBudget = $mBudget;
        $this->mProposalBudget = $mProposalBudget;
        $this->mExpenseBudget = $mExpenseBudget;
        $this->hHelperHandleTotalAmount = $hHelperHandleTotalAmount;
        $this->hHelperGuzzleService = $hHelperGuzzleService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $budgets = $this->mBudget::all();
        return view('budget.list', compact('budgets'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $apiConnect = Auth::user()->accessToken;
        return view('budget.create', compact('apiConnect'));
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
            if(Auth::user()->accessToken) {
                $dataBudget = [];
                $dataBudget['Name'] = $request->input('name');
                $dataBudget['Year__c'] = $request->input('year__c');
                $dataBudget['Approval_Status__c'] = 'Pending';

                // Call api insert budget.
                $response = $this->hHelperGuzzleService::guzzlePost(config('authenticate.api_uri').'/Budget__c/', Auth::user()->accessToken, $dataBudget);

                // if insert sf false.
                if(isset($response->success) && $response->success == false) {
                    // if status code 401, call again insert
                    if($response->statusCode == 401) {
                        $resFreshToken = $this->hHelperGuzzleService::refreshToken(Auth::user()->refreshToken);

                        if($resFreshToken->success == true){
                            $access_token = $resFreshToken->access_token;

                            $response1 = $this->hHelperGuzzleService::guzzlePost(config('authenticate.api_uri').'/Budget__c/', $access_token, $dataBudget);

                            if(isset($response1->success) && $response1->success == true) {
                                // set sf id
                                $sfid = $response1->id;
                            }
                        }
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
            $requestData['name'] = $request->input('name');
            $requestData['year__c'] = $request->input('year__c');
            $requestData['total_amount__c'] = 0;
            $requestData['sfid'] = $sfid;

            $budget = $this->mBudget->create($requestData);

            DB::commit();

            return redirect('budget/'.$budget->id);
        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Store - BudgetController');
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

            $budget = $this->mBudget->findOrFail($id);
            $proposal_budget = $this->mProposalBudget->where('budget__c', $budget->sfid)->with('proposal')->get();
            $expense_budget = $this->mExpenseBudget->where('budget__c', $budget->sfid)->with('expense')->get();
            $listApprovalProcesses = [];
            if($budget->status_approve == true) {
                $listApprovalProcesses = $this->hHelperGuzzleService->guzzleGetApproval(Auth::user()->accessToken, $budget->sfid);
            }

            return view('budget.detail', [
                'budget' => $budget,
                'proposal' => $proposal_budget,
                'expense' => $expense_budget,
                'listApprovalProcesses' => $listApprovalProcesses
            ]);
        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Show - BudgetController');
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
            $budget = $this->mBudget->findOrFail($id);

            if($budget->status_approve) return redirect('/budget');
            return view('budget.edit', ['budget' => $budget]);
        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Edit - BudgetController');
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
            $validator = Validator::make($request->all(), $this->validation());
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $budget = $this->mBudget::findOrFail($id);

            // Flag flagUpdate check update salesforce true or false.
            $flagUpdate = false;

            // Check and update to salesforce.
            if(Auth::user()->accessToken) {
                $dataBudget = [];
                $dataBudget['Name'] = $request->input('name');
                $dataBudget['Year__c'] = $request->input('year__c');

                $response = $this->hHelperGuzzleService::guzzleUpdate(config('authenticate.api_uri').'/Budget__c/'.$budget->sfid, Auth::user()->accessToken, $dataBudget);
                if(isset($response->success) && $response->success == false) {

                    if($response->statusCode == 401) {
                        $resFreshToken = $this->hHelperGuzzleService::refreshToken(Auth::user()->refreshToken);

                        if($resFreshToken->success == true){
                            $access_token = $resFreshToken->access_token;

                            $response1 = $this->hHelperGuzzleService::guzzleUpdate(config('authenticate.api_uri').'/Budget__c/'.$budget->sfid, $access_token, $dataBudget);

                            if(isset($response1->success) && $response1->success == true) {
                                $flagUpdate = true;
                            }
                        }
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
            $requestData['name'] = $request->input('name');
            $requestData['year__c'] = $request->input('year__c');

            $budget->update($requestData);

            DB::commit();

            return redirect('budget/'. $budget->id);
        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Update - BudgetController');
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
        try {
            $budget = $this->mBudget->findOrFail($id);

            // Flag flagDelete check delete salesforce true or false.
            $flagDelete = false;

            if(Auth::user()->accessToken) {

                $response = $this->hHelperGuzzleService::guzzleDelete(config('authenticate.api_uri').'/Budget__c/'.$budget->sfid, Auth::user()->accessToken);

                if(isset($response->success) && $response->success == false) {
                    if($response->statusCode == 401) {
                        $resFreshToken = $this->hHelperGuzzleService::refreshToken(Auth::user()->refreshToken);

                        if($resFreshToken->success == true){
                            $access_token = $resFreshToken->access_token;

                            $response1 = $this->hHelperGuzzleService::guzzleDelete(config('authenticate.api_uri').'/Budget__c/'.$budget->sfid, $access_token);

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
                return redirect()->back()->withErrors(['message' => __('messages.Token_Error')])->withInput();
            }

            DB::beginTransaction();
            $listProposalBudget = $this->mProposalBudget->where('budget__c', $budget->sfid);
            $arrProposal = $listProposalBudget->pluck('proposal__c')->toArray();
            $listProposalBudget->delete();
            $listExpenseBudget = $this->mExpenseBudget->where('budget__c', $budget->sfid);
            $arrExpense = $listExpenseBudget->pluck('expense__c')->toArray();
            $listExpenseBudget->delete();
            $budget->delete();
            DB::commit();

            $this->hHelperHandleTotalAmount->caseDeleteParent('budget', $arrProposal, $arrExpense);

            if($request->ajax()){
                return response()->json(['success' => true]);
            }
            return redirect('budget');

        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Destroy - BudgetController');
            DB::rollback();

            if($request->ajax()){
                return response()->json(['success' => false]);
            }
            return redirect()->back()->withErrors(['message' => __('messages.System_Error')])->withInput();
        }
    }

    public function submitApproval(Request $request) {
        try {
            $id = $request->input('id');
            $budget = $this->mBudget::findOrFail($id);
            $response = $this->hHelperGuzzleService->submitApproval(Auth::user()->accessToken, $budget->sfid);

            if($response->success == true) {
                $budget->status_approve = true;
                $budget->save();
                return redirect('budget/'. $id);
            }

            return redirect()->back()->withErrors(['message' => __('messages.System_Error')]);

        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- submitApproval - BudgetController');
            return redirect()->back()->withErrors(['message' => __('messages.System_Error')]);
        }

    }

    private function validation() {
        return [
            'name' => 'required|max:80',
            'year__c' => 'required|max:4',
        ];
    }
}

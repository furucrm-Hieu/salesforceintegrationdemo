<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Budget;
use App\Models\ExpenseBudget;
use Illuminate\Support\Facades\Auth;
use App\Helpers\HelperHandleTotalAmount;
use App\Helpers\HelperGuzzleService;
use App\Helpers\HelperConvertDateTime;
use DB, Session;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ExpenseBudgetController extends Controller
{
    protected $mExpense;
    protected $mBudget;
    protected $mExpenseBudget;
    protected $hHelperConvertDateTime;
    protected $hHelperHandleTotalAmount;
    protected $hHelperGuzzleService;

    public function __construct(Expense $mExpense, ExpenseBudget $mExpenseBudget, Budget $mBudget, HelperHandleTotalAmount $hHelperHandleTotalAmount, HelperGuzzleService $hHelperGuzzleService, HelperConvertDateTime $hHelperConvertDateTime) {
        $this->mExpense = $mExpense;
        $this->mBudget = $mBudget;
        $this->mExpenseBudget = $mExpenseBudget;
        $this->hHelperHandleTotalAmount = $hHelperHandleTotalAmount;
        $this->hHelperGuzzleService = $hHelperGuzzleService;
        $this->hHelperConvertDateTime = $hHelperConvertDateTime;
    }

    public function createJunction($id) {

        try {
            $expenseBudget = new $this->mExpenseBudget([
                'expense__c' => '',
                'budget__c' => '',
                'amount__c' => ''
            ]);

            $dataCheckType = explode("-", $id);

            if($dataCheckType[0] == 'expense') {
                $expenseBudget->expense__c = $this->mExpense::findOrFail($dataCheckType[1])->sfid;
            }elseif ($dataCheckType[0]  == 'budget') {
                $expenseBudget->budget__c = $this->mBudget->findOrFail($dataCheckType[1])->sfid;
            }
            else {
                abort(404);
            }

            $apiConnect = Auth::user()->accessToken;
            $expenses = $this->mExpense->orderBy('name')->get()->pluck('name','sfid');
            $budgets = $this->mBudget->orderBy('name')->get()->pluck('name','sfid');
            $linkRedirect = url($dataCheckType[0].'/'.$dataCheckType[1]);
            $type = 'create';

            return view('expense_budget.create', compact('expenseBudget', 'expenses', 'budgets', 'linkRedirect', 'apiConnect', 'type'));
        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- createJunction - ExpenseBudgetController');
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

                $dataExpBud = [];
                $dataExpBud['Budget__c'] = $request->input('budget__c');
                $dataExpBud['Expense__c'] = $request->input('expense__c');
                $dataExpBud['Amount__c'] = $request->input('amount');
    
                $response = $this->hHelperGuzzleService::guzzlePost(config('authenticate.api_uri').'/Expense_Budget__c/', Auth::user()->accessToken, $dataExpBud);
                
                // if insert sf false and status code 401, call again insert.
                if(isset($response->success) && $response->success == false) {
                    if($response->statusCode == 401) {

                        $resFreshToken = $this->hHelperGuzzleService::refreshToken(Auth::user()->refreshToken);
                        if($resFreshToken->success == true){
                            $access_token = $resFreshToken->access_token;

                            $response1 = $this->hHelperGuzzleService::guzzlePost(config('authenticate.api_uri').'/Expense_Budget__c/', $access_token, $dataExpBud);

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
            $requestData['expense__c'] = $request->input('expense__c');
            $requestData['amount__c'] = $request->input('amount');
            $requestData['sfid'] = $sfid;

            $expenseBudget = $this->mExpenseBudget->create($requestData);

            DB::commit();

            $this->hHelperHandleTotalAmount->caseCreateDeleteJunction('', $expenseBudget->expense__c, $expenseBudget->budget__c);

            return redirect($request->linkRedirect);
                        
        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Store - ExpenseBudgetController');
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

            $expenseBudget = $this->mExpenseBudget::findOrFail($id);
            $expense = $this->mExpense::where('sfid', $expenseBudget->expense__c)->first();
            $linkRedirect = url('expense/'.$expense->id);

            if($expenseBudget->status_approve == $this->hHelperConvertDateTime::SUBMIT){
                return redirect($linkRedirect);
            }
            
            $expenses = $this->mExpense::orderBy('name')->get()->pluck('name', 'sfid');
            $budgets = $this->mBudget::orderBy('name')->get()->pluck('name', 'sfid');
            $apiConnect = Auth::user()->accessToken; 
            $type = 'edit';
            
            return view('expense_budget.edit', compact('expenseBudget', 'expenses', 'budgets', 'apiConnect', 'linkRedirect', 'type'));
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
        try {
            // Check validator
            $validator = Validator::make($request->all(), $this->validation_edit());
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $expenseBudget = $this->mExpenseBudget::findOrFail($id);

            // Flag flagUpdate check update salesforce true or false.
            $flagUpdate = false;

            // Check and update to salesforce.
            if(!empty(Auth::user()->accessToken)) {

                $dataExpBud = [];
                $dataExpBud['Amount__c'] = $request->input('amount');

                $response = $this->hHelperGuzzleService::guzzleUpdate(config('authenticate.api_uri').'/Expense_Budget__c/'.$expenseBudget->sfid, Auth::user()->accessToken, $dataExpBud);

                if(isset($response->success) && $response->success == false) {
                    if($response->statusCode == 401) {
                        $resFreshToken = $this->hHelperGuzzleService::refreshToken(Auth::user()->refreshToken);

                        if($resFreshToken->success == true){
                            $access_token = $resFreshToken->access_token;

                            $response1 = $this->hHelperGuzzleService::guzzleUpdate(config('authenticate.api_uri').'/Expense_Budget__c/'.$expenseBudget->sfid, $access_token, $dataExpBud);

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

            $expenseBudget->update($requestData);

            DB::commit();

            $this->hHelperHandleTotalAmount->caseCreateDeleteJunction('', $expenseBudget->expense__c, $expenseBudget->budget__c);

            return redirect($request->linkRedirect);    

        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Update - ExpenseBudgetController');
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

            $junctionEB = $this->mExpenseBudget::findOrFail($id);

            // Flag flagDelete check delete salesforce true or false.
            $flagDelete = false;

            if(!empty(Auth::user()->accessToken)) {

                $response = $this->hHelperGuzzleService::guzzleDelete(config('authenticate.api_uri').'/Expense_Budget__c/'.$junctionEB->sfid, Auth::user()->accessToken);
                
                if(isset($response->success) && $response->success == false) {
                    if($response->statusCode == 401) {
                        $resFreshToken = $this->hHelperGuzzleService::refreshToken(Auth::user()->refreshToken);
    
                        if($resFreshToken->success == true){
                            $access_token = $resFreshToken->access_token;

                            $response1 = $this->hHelperGuzzleService::guzzleDelete(config('authenticate.api_uri').'/Expense_Budget__c/'.$expense->sfid, $access_token);

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
            $junctionEB->delete();
            DB::commit();

            $this->hHelperHandleTotalAmount->caseCreateDeleteJunction('', $junctionEB->expense__c, $junctionEB->budget__c);

            if($request->ajax()){
                return response()->json(['success' => true]);
            }
            return redirect('expense');

        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Destroy - ExpenseBudgetController');
            DB::rollback();

            if($request->ajax()){
                return response()->json(['success' => false]);
            }
            return redirect()->back()->withErrors(['message' => __('messages.System_Error')])->withInput();
        }
    }

    private function validation() {
        return [
            'budget__c' => 'required',
            'expense__c' => 'required',
            'amount' => 'max:12',
        ];
    }

    private function validation_edit() {
        return [
            'amount' => 'max:12',
        ];
    }
}

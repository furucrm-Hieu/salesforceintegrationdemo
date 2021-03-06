<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Budget;
use App\Models\ExpenseBudget;
use Illuminate\Support\Facades\Auth;
use App\Helpers\HelperConvertDateTime;
use App\Helpers\HelperHandleTotalAmount;
use App\Helpers\HelperGuzzleService;
use DB, Session;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    protected $mExpense;
    protected $mBudget;
    protected $mExpenseBudget;
    protected $hHelperConvertDateTime;
    protected $hHelperHandleTotalAmount;
    protected $hHelperGuzzleService;

    public function __construct(Expense $mExpense, Budget $mBudget, ExpenseBudget $mExpenseBudget, HelperConvertDateTime $hHelperConvertDateTime, HelperHandleTotalAmount $hHelperHandleTotalAmount, HelperGuzzleService $hHelperGuzzleService) {
        $this->mExpense = $mExpense;
        $this->mBudget = $mBudget;
        $this->mExpenseBudget = $mExpenseBudget;
        $this->hHelperConvertDateTime = $hHelperConvertDateTime;
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
        $expenses = $this->mExpense::all();
        return view('expense.list', compact('expenses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $apiConnect = Auth::user()->accessToken;
        return view('expense.create', compact('apiConnect'));
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

            // Check access token in database is exist.
            if(!empty(Auth::user()->accessToken)) {

                $dataExpense = [];
                $dataExpense['Name'] = $request->input('name');
                $dataExpense['Year__c'] = $request->input('year');
                $dataExpense['Details__c'] = $request->input('detail');
                $dataExpense['Approved_At__c'] = $this->hHelperConvertDateTime->convertDateTimeCallApi($request->input('approved_at'));
                $dataExpense['Proposed_At__c'] = $this->hHelperConvertDateTime->convertDateTimeCallApi($request->input('proposed_at'));
                $dataExpense['Approval_Status__c'] = 'Pending';

                // Call api insert proposal.
                $response = $this->hHelperGuzzleService::guzzlePost(config('authenticate.api_uri').'/Expense__c/', Auth::user()->accessToken, $dataExpense);

                // if insert sf false.
                if(isset($response->success) && $response->success == false) {
                    // if status code 401, call again insert
                    if($response->statusCode == 401) {
                        $resFreshToken = $this->hHelperGuzzleService::refreshToken(Auth::user()->refreshToken);

                        if($resFreshToken->success == true){
                            $access_token = $resFreshToken->access_token;

                            $response1 = $this->hHelperGuzzleService::guzzlePost(config('authenticate.api_uri').'/Expense__c/', $access_token, $dataExpense);

                            if(isset($response1->success) && $response1->success == true) {
                                // set sf id
                                $sfid = $response1->id;
                            }
                        }
                    }
                // if insert sf true.
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
            $requestData['year__c'] = $request->input('year');
            $requestData['details__c'] = $request->input('detail');
            $requestData['total_amount__c'] = 0;
            $requestData['status_approve'] = $this->hHelperConvertDateTime::PENDING;
            $requestData['approved_at__c'] = $this->hHelperConvertDateTime->convertDateTimeJpToUtc($request->input('approved_at'));
            $requestData['proposed_at__c'] = $this->hHelperConvertDateTime->convertDateTimeJpToUtc($request->input('proposed_at'));
            $requestData['sfid'] = $sfid;

            $expense = $this->mExpense->create($requestData);

            DB::commit();

            return redirect('expense/'. $expense->id);

        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Store - ExpenseController');
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

            $expense = $this->mExpense::findOrFail($id);
            $listBudget = $this->mExpenseBudget->where('expense__c', $expense->sfid)->with('budget')->get();
            $listApprovalProcesses = [];
            if($expense->status_approve != $this->hHelperConvertDateTime::PENDING) {
                $listApprovalProcesses = $this->hHelperGuzzleService->guzzleGetApproval(Auth::user()->accessToken, $expense->sfid);
                if($listApprovalProcesses) {
                    if($listApprovalProcesses[0]['Status'] == $this->hHelperConvertDateTime::APPROVED){
                        $newStatus = $this->hHelperConvertDateTime::APPROVED;
                    }else if($listApprovalProcesses[0]['Status'] == $this->hHelperConvertDateTime::REJECTED) {
                        $newStatus = $this->hHelperConvertDateTime::PENDING;
                        $expense->type_submit = false;
                    }else {
                        $newStatus = $this->hHelperConvertDateTime::SUBMIT;
                        $expense->type_submit = false;
                    }
                    $expense->status_approve = $newStatus;
                    $expense->save();
                }
            }

            return view('expense.show', compact('expense', 'listBudget', 'listApprovalProcesses'));

        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Show - ExpenseController');
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

            $apiConnect = Auth::user()->accessToken;
            $expense = $this->mExpense::findOrFail($id);

            if($expense->status_approve == $this->hHelperConvertDateTime::SUBMIT){
                return redirect()->back();
            }

            return view('expense.edit', compact('expense', 'apiConnect'));

        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Edit - ExpenseController');
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

            $expense = $this->mExpense::findOrFail($id);

            // Flag flagUpdate check update salesforce true or false.
            $flagUpdate = false;

            // Check and update to salesforce.
            if(!empty(Auth::user()->accessToken)) {
                $dataExpense = [];
                $dataExpense['Name'] = $request->input('name');
                $dataExpense['Year__c'] = $request->input('year');
                $dataExpense['Details__c'] = $request->input('detail');
                $dataExpense['Approved_At__c'] = $this->hHelperConvertDateTime->convertDateTimeCallApi($request->input('approved_at'));
                $dataExpense['Proposed_At__c'] = $this->hHelperConvertDateTime->convertDateTimeCallApi($request->input('proposed_at'));

                // Call api update expense.
                $response = $this->hHelperGuzzleService::guzzleUpdate(config('authenticate.api_uri').'/Expense__c/'.$expense->sfid, Auth::user()->accessToken, $dataExpense);

                // if update sf false.
                if(isset($response->success) && $response->success == false) {
                    // if status code 401, call again insert
                    if($response->statusCode == 401) {
                        $resFreshToken = $this->hHelperGuzzleService::refreshToken(Auth::user()->refreshToken);

                        if($resFreshToken->success == true){
                            $access_token = $resFreshToken->access_token;

                            $response1 = $this->hHelperGuzzleService::guzzleUpdate(config('authenticate.api_uri').'/Expense__c/'.$proposal->sfid, $access_token, $dataExpense);

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
            $requestData['year__c'] = $request->input('year');
            $requestData['details__c'] = $request->input('detail');
            $requestData['approved_at__c'] = $this->hHelperConvertDateTime->convertDateTimeJpToUtc($request->input('approved_at'));
            $requestData['proposed_at__c'] = $this->hHelperConvertDateTime->convertDateTimeJpToUtc($request->input('proposed_at'));

            $expense->update($requestData);

            DB::commit();

            return redirect('expense/'.$expense->id);

        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Update - ExpenseController');
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

            $expense = $this->mExpense::findOrFail($id);

            // Flag flagDelete check delete salesforce true or false.
            $flagDelete = false;

            if(!empty(Auth::user()->accessToken)) {

                $response = $this->hHelperGuzzleService::guzzleDelete(config('authenticate.api_uri').'/Expense__c/'.$expense->sfid, Auth::user()->accessToken);

                if(isset($response->success) && $response->success == false) {
                    if($response->statusCode == 401) {
                        $resFreshToken = $this->hHelperGuzzleService::refreshToken(Auth::user()->refreshToken);

                        if($resFreshToken->success == true){
                            $access_token = $resFreshToken->access_token;

                            $response1 = $this->hHelperGuzzleService::guzzleDelete(config('authenticate.api_uri').'/Expense__c/'.$expense->sfid, $access_token);

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
            $listExpenseBudget = $this->mExpenseBudget->where('expense__c', $expense->sfid);
            $arrBudget = $listExpenseBudget->pluck('budget__c')->toArray();
            $listExpenseBudget->delete();
            $expense->delete();
            DB::commit();

            $this->hHelperHandleTotalAmount->caseDeleteParent('expense', $arrBudget);

            if($request->ajax()){
                return response()->json(['success' => true]);
            }
            return redirect('expense');

        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Destroy - ExpenseController');
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
            $expense = $this->mExpense::findOrFail($id);

            $response = $this->hHelperGuzzleService->submitApproval(Auth::user()->accessToken, $expense->sfid);

            if($response->success == false && $response->statusCode == 400) {
                return redirect()->back()->withErrors(['message' => $response->message])->withInput();
            }

            if($response->success == true) {
                $expense->status_approve = $this->hHelperConvertDateTime::SUBMIT;
                $expense->save();
                return redirect('expense/'. $id);
            }

            return redirect()->back()->withErrors(['message' => __('messages.Token_Error')]);

        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- submitApproval - ExpenseController');
            return redirect()->back()->withErrors(['message' => __('messages.System_Error')]);
        }

    }

    private function validation() {
        return [
            'name' => 'required|max:80',
            'year' => 'required|max:4',
            'proposed_at' => 'required|date',
            'approved_at' => 'required|date',
            'detail' => 'max:200',
        ];
    }
}

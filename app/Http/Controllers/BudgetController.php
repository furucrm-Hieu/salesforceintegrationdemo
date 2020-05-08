<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Budget;
use App\Models\ProposalBudget;
use App\Models\ApiConnect;
use App\Helpers\HelperHandleTotalAmount;
use App\Helpers\HelperGuzzleService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class BudgetController extends Controller
{
    protected $mBudget; 
    protected $mProposalBudget;
    protected $hHelperHandleTotalAmount;
    protected $hHelperGuzzleService;
    protected $vApiConnect;

    public function __construct(Budget $mBudget, ProposalBudget $mProposalBudget, HelperHandleTotalAmount $hHelperHandleTotalAmount, HelperGuzzleService $hHelperGuzzleService) {
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
        $apiConnect = $this->vApiConnect;
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
        DB::beginTransaction();
        try {

            $validator = Validator::make($request->all(), $this->validation());
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $requestData = [];
            $requestData['name'] = $request->input('name');
            $requestData['year__c'] = $request->input('year__c');
            $requestData['total_amount__c'] = 0;

            $budget = $this->mBudget->create($requestData);

            DB::commit();
            
            if($this->vApiConnect && $this->vApiConnect->expried == false) {

                $dataBudget = [];
                $dataBudget['Name'] = $request->input('name');
                $dataBudget['Year__c'] = $request->input('year__c');

                $response = $this->hHelperGuzzleService::guzzlePost(config('authenticate.api_uri').'/Budget__c/', $this->vApiConnect->accessToken, $dataBudget);
                $response = json_decode($response);

                if(isset($response->success) && $response->success == true) {
                    $budget->update(['sfid' => $response->id]);
                }else if(isset($response->success) && $response->success == false) {
                    $resFreshToken = $this->hHelperGuzzleService::refreshToken($this->vApiConnect->refreshToken);
                    if(json_decode($resFreshToken)->success == true){
                        $access_token = json_decode($resFreshToken)->access_token;

                        $response1 = $this->hHelperGuzzleService::guzzlePost(config('authenticate.api_uri').'/Budget__c/', $access_token, $dataBudget);
                        $response1 = json_decode($response1);
                        if(isset($response1->success) && $response1->success == true) {
                            $budget->update(['sfid' => $response1->id]);
                        }
                    }
                }
            }

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

            if($budget->sfid == null) {
                $proposal_budget = [];
            }else {
                $proposal_budget = $this->mProposalBudget->where('budget__c', $budget->sfid)
                    ->with('proposal')->get();
            }
            
            return view('budget.detail', ['budget' => $budget, 'proposal' => $proposal_budget]);
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
            
            $apiConnect = $this->vApiConnect;
            $budget = $this->mBudget->findOrFail($id);

            return view('budget.create', ['budget' => $budget, 'apiConnect' => $apiConnect]);
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
        DB::beginTransaction();
        try {

            $validator = Validator::make($request->all(), $this->validation());
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $budget = $this->mBudget::findOrFail($id);
            $budget->update($request->all());
            
            DB::commit();

            if($this->vApiConnect && $this->vApiConnect->expried == false) {

                $dataBudget = [];
                $dataBudget['Name'] = $request->input('name');
                $dataBudget['Year__c'] = $request->input('year__c');

                $response = $this->hHelperGuzzleService::guzzleUpdate(config('authenticate.api_uri').'/Budget__c/'.$budget->sfid, $this->vApiConnect->accessToken, $dataBudget);
                $response = json_decode($response);

                if(isset($response->success) && $response->success == false) {
                    $resFreshToken = $this->hHelperGuzzleService::refreshToken($this->vApiConnect->refreshToken);

                    if(json_decode($resFreshToken)->success == true){
                        $access_token = json_decode($resFreshToken)->access_token;
                        $response1 = $this->hHelperGuzzleService::guzzleUpdate(config('authenticate.api_uri').'/Budget__c/'.$budget->sfid, $access_token, $dataBudget);
                    }
                }
            }

            return redirect('budget/'.$id);
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
        DB::beginTransaction();
        try {
            
            $budget = $this->mBudget->findOrFail($id);
            $listProposalBudget = $this->mProposalBudget->where('budget__c', $budget->sfid)->delete();
            $budget->delete();
            DB::commit();
            $this->hHelperHandleTotalAmount->caseDeleteParentOrJunction('budget');
            
            if($this->vApiConnect && $this->vApiConnect->expried == false) {
                $response = $this->hHelperGuzzleService::guzzleDelete(config('authenticate.api_uri').'/Budget__c/'.$budget->sfid, $this->vApiConnect->accessToken);

                $response = json_decode($response);
                if(isset($response->success) && $response->success == false) {
                    $resFreshToken = $this->hHelperGuzzleService::refreshToken($this->vApiConnect->refreshToken);
                    
                    if(json_decode($resFreshToken)->success == true){
                        $access_token = json_decode($resFreshToken)->access_token;
                        $response1 = $this->hHelperGuzzleService::guzzleDelete(config('authenticate.api_uri').'/Budget__c/'.$budget->sfid, $access_token);
                    }
                }
            }

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

    private function validation() {
        return [
            'name' => 'required|max:80',
            'year__c' => 'required|max:4',
        ];
    }
}

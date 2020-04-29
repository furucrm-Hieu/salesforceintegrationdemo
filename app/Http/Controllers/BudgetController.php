<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Budget;
use App\Models\ProposalBudget;
use App\Helpers\HelperHandleTotalAmount;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class BudgetController extends Controller
{
    protected $mBudget; 
    protected $mProposalBudget;
    protected $hHelperHandleTotalAmount;

    public function __construct(Budget $mBudget, ProposalBudget $mProposalBudget, HelperHandleTotalAmount $hHelperHandleTotalAmount) {
        $this->mBudget = $mBudget;
        $this->mProposalBudget = $mProposalBudget;
        $this->hHelperHandleTotalAmount = $hHelperHandleTotalAmount;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('budget.list', ['budgets' => $this->mBudget::whereNotNull('sfid')->get()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {   
        return view('budget.create');
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

            $budget['total_amount__c'] = 0;
            $budget['external_id__c'] = uniqid(Str::random(5));
            $budgetId = $this->mBudget->insertGetId($budget);
            DB::commit();
            return redirect()->route('budget.show', ['budget' => $budgetId]);
        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Store - BudgetController');
            DB::rollback();
            return redirect()->back()->withErrors(['message' => 'System error, Please contact admin'])->withInput();
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
                $proposal_budget = $this->mProposalBudget->whereNotNull('budget__c')
                    ->whereNotNull('proposal__c')
                    ->whereNotNull('amount__c')
                    ->whereNotNull('external_id__c')
                    ->whereNotNull('id')
                    ->where('budget__c', $budget->sfid)
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
            $budget = $this->mBudget->findOrFail($id);

            return view('budget.create', ['budget' => $budget]);
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

            $this->mBudget->findOrFail($id)->update($request->all());
            
            DB::commit();
            return redirect('budget/'.$id);
        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Update - BudgetController');
            DB::rollback();
            return redirect()->back()->withErrors(['message' => 'System error, Please contact admin'])->withInput();
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

            return redirect()->back()->withErrors(['message' => 'System error, Please contact admin'])->withInput();
        }
    }

    private function validation() {
        return [
            'name' => 'required|max:80',
            'year__c' => 'required|max:4',
        ];
    }
}

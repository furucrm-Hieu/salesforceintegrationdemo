<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proposal;
use App\Models\Budget;
use App\Models\ProposalBudget;
use App\Helpers\HelperConvertDateTime;
use App\Helpers\HelperHandleTotalAmount;
use DB, Session;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProposalController extends Controller
{
    protected $mProposal;
    protected $mBudget;
    protected $mProposalBudget;
    protected $hHelperConvertDateTime;
    protected $hHelperHandleTotalAmount;

    public function __construct(Proposal $mProposal, ProposalBudget $mProposalBudget, Budget $mBudget, HelperConvertDateTime $hHelperConvertDateTime, HelperHandleTotalAmount $hHelperHandleTotalAmount) {
        $this->mProposal = $mProposal;
        $this->mBudget = $mBudget;
        $this->mProposalBudget = $mProposalBudget;
        $this->hHelperConvertDateTime = $hHelperConvertDateTime;
        $this->hHelperHandleTotalAmount = $hHelperHandleTotalAmount;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $proposals = $this->mProposal::whereNotNull('sfid')->get();
        return view('proposal.list', compact('proposals'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {   
        return view('proposal.create');
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
            $requestData['year__c'] = $request->input('year');
            $requestData['details__c'] = $request->input('detail');
            $requestData['total_amount__c'] = 0;
            $requestData['approved_at__c'] = $this->hHelperConvertDateTime->convertDateTimeJpToUtc($request->input('approved_at'));
            $requestData['proposed_at__c'] = $this->hHelperConvertDateTime->convertDateTimeJpToUtc($request->input('proposed_at'));
            $requestData['external_id__c'] = uniqid(Str::random(5));

            $proposal = $this->mProposal->create($requestData);

            DB::commit();
            return redirect('proposal/'.$proposal->id);

        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Store - ProposalController');
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
            $proposal = $this->mProposal::findOrFail($id);

            if($proposal->sfid == null) {
                $listBudget = [];
            }
            else {
                $listBudget = $this->mProposalBudget->whereNotNull('proposal__c')
                    ->whereNotNull('budget__c')
                    ->whereNotNull('amount__c')
                    ->whereNotNull('external_id__c')
                    ->whereNotNull('id')
                    ->where('proposal__c', $proposal->sfid)
                    ->with('budget')
                    ->get();
            }
            
            return view('proposal.show', compact('proposal', 'listBudget'));
        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Show - ProposalController');
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
            $proposal = $this->mProposal::findOrFail($id);

            return view('proposal.edit', compact('proposal'));
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

            $proposal = $this->mProposal::findOrFail($id);

            $requestData = [];
            $requestData['name'] = $request->input('name');
            $requestData['year__c'] = $request->input('year');
            $requestData['details__c'] = $request->input('detail');
            $requestData['approved_at__c'] = $this->hHelperConvertDateTime->convertDateTimeJpToUtc($request->input('approved_at'));
            $requestData['proposed_at__c'] = $this->hHelperConvertDateTime->convertDateTimeJpToUtc($request->input('proposed_at'));

            $proposal->update($requestData);

            DB::commit();
            return redirect('proposal/'.$proposal->id);

        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Update - ProposalController');
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

            $proposal = $this->mProposal::findOrFail($id);
            $listProposalBudget = $this->mProposalBudget->where('proposal__c', $proposal->sfid)->delete();
            $proposal->delete();
            DB::commit();
            $this->hHelperHandleTotalAmount->caseDeleteParentOrJunction('proposal');

            if($request->ajax()){
                return response()->json(['success' => true]);
            }

            return redirect('proposal');
        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Destroy - ProposalController');
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
            'year' => 'required|max:4',
            'proposed_at' => 'required|date',
            'approved_at' => 'required|date',
            'detail' => 'max:200',
        ];
    }
}

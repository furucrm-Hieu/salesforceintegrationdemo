<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proposal;
use App\Models\Budget;
use App\Models\ProposalBudget;
use App\Helpers\HelperConvertDateTime;
use App\Helpers\HelperHandleTotalAmount;
use App\Helpers\HelperGuzzleService;
use DB, Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProposalController extends Controller
{
    protected $mProposal;
    protected $mBudget;
    protected $mProposalBudget;
    protected $hHelperConvertDateTime;
    protected $hHelperHandleTotalAmount;
    protected $hHelperGuzzleService;

    public function __construct(Proposal $mProposal, ProposalBudget $mProposalBudget, Budget $mBudget, HelperConvertDateTime $hHelperConvertDateTime, HelperHandleTotalAmount $hHelperHandleTotalAmount, HelperGuzzleService $hHelperGuzzleService) {
        $this->mProposal = $mProposal;
        $this->mBudget = $mBudget;
        $this->mProposalBudget = $mProposalBudget;
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
        $proposals = $this->mProposal::all();
        return view('proposal.list', compact('proposals'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $apiConnect = Auth::user()->accessToken;
        return view('proposal.create', compact('apiConnect'));
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

                $dataProposal = [];
                $dataProposal['Name'] = $request->input('name');
                $dataProposal['Year__c'] = $request->input('year');
                $dataProposal['Details__c'] = $request->input('detail');
                $dataProposal['Approved_At__c'] = $this->hHelperConvertDateTime->convertDateTimeCallApi($request->input('approved_at'));
                $dataProposal['Proposed_At__c'] = $this->hHelperConvertDateTime->convertDateTimeCallApi($request->input('proposed_at'));
                $dataProposal['Approval_Status__c'] = 'Pending';

                // Call api insert proposal.
                $response = $this->hHelperGuzzleService::guzzlePost(config('authenticate.api_uri').'/Proposal__c/', Auth::user()->accessToken, $dataProposal);

                // if insert sf false.
                if(isset($response->success) && $response->success == false) {
                    // if status code 401, call again insert
                    if($response->statusCode == 401) {
                        $resFreshToken = $this->hHelperGuzzleService::refreshToken(Auth::user()->refreshToken);

                        if($resFreshToken->success == true){
                            $access_token = $resFreshToken->access_token;

                            $response1 = $this->hHelperGuzzleService::guzzlePost(config('authenticate.api_uri').'/Proposal__c/', $access_token, $dataProposal);

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

            $proposal = $this->mProposal->create($requestData);

            DB::commit();

            return redirect('proposal/'. $proposal->id);

        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Store - ProposalController');
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

            $proposal = $this->mProposal::findOrFail($id);
            $listBudget = $this->mProposalBudget->where('proposal__c', $proposal->sfid)->with('budget')->get();
            $listApprovalProcesses = [];
            if($proposal->status_approve != $this->hHelperConvertDateTime::PENDING) {
                $listApprovalProcesses = $this->hHelperGuzzleService->guzzleGetApproval(Auth::user()->accessToken, $proposal->sfid);
                $newStatus = ($listApprovalProcesses[0]['Status'] == $this->hHelperConvertDateTime::APPROVED) ? $this->hHelperConvertDateTime::APPROVED : $this->hHelperConvertDateTime::SUBMIT;
                $proposal->status_approve = $newStatus;
                $proposal->save();
            }

            return view('proposal.show', compact('proposal', 'listBudget', 'listApprovalProcesses'));

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

            $apiConnect = Auth::user()->accessToken;
            $proposal = $this->mProposal::findOrFail($id);

            return view('proposal.edit', compact('proposal', 'apiConnect'));

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
            $validator = Validator::make($request->all(), $this->validation());
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $proposal = $this->mProposal::findOrFail($id);

            // Flag flagUpdate check update salesforce true or false.
            $flagUpdate = false;

            // Check and update to salesforce.
            if(!empty(Auth::user()->accessToken)) {
                $dataProposal = [];
                $dataProposal['Name'] = $request->input('name');
                $dataProposal['Year__c'] = $request->input('year');
                $dataProposal['Details__c'] = $request->input('detail');
                $dataProposal['Approved_At__c'] = $this->hHelperConvertDateTime->convertDateTimeCallApi($request->input('approved_at'));
                $dataProposal['Proposed_At__c'] = $this->hHelperConvertDateTime->convertDateTimeCallApi($request->input('proposed_at'));

                // Call api update proposal.
                $response = $this->hHelperGuzzleService::guzzleUpdate(config('authenticate.api_uri').'/Proposal__c/'.$proposal->sfid, Auth::user()->accessToken, $dataProposal);

                // if update sf false.
                if(isset($response->success) && $response->success == false) {
                    // if status code 401, call again insert
                    if($response->statusCode == 401) {
                        $resFreshToken = $this->hHelperGuzzleService::refreshToken(Auth::user()->refreshToken);

                        if($resFreshToken->success == true){
                            $access_token = $resFreshToken->access_token;

                            $response1 = $this->hHelperGuzzleService::guzzleUpdate(config('authenticate.api_uri').'/Proposal__c/'.$proposal->sfid, $access_token, $dataProposal);

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

            $proposal->update($requestData);

            DB::commit();

            return redirect('proposal/'.$proposal->id);

        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- Update - ProposalController');
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

            $proposal = $this->mProposal::findOrFail($id);

            // Flag flagDelete check delete salesforce true or false.
            $flagDelete = false;

            if(!empty(Auth::user()->accessToken)) {

                $response = $this->hHelperGuzzleService::guzzleDelete(config('authenticate.api_uri').'/Proposal__c/'.$proposal->sfid, Auth::user()->accessToken);

                if(isset($response->success) && $response->success == false) {
                    if($response->statusCode == 401) {
                        $resFreshToken = $this->hHelperGuzzleService::refreshToken(Auth::user()->refreshToken);

                        if($resFreshToken->success == true){
                            $access_token = $resFreshToken->access_token;

                            $response1 = $this->hHelperGuzzleService::guzzleDelete(config('authenticate.api_uri').'/Proposal__c/'.$proposal->sfid, $access_token);

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
            $listProposalBudget = $this->mProposalBudget->where('proposal__c', $proposal->sfid);
            $arrBudget = $listProposalBudget->pluck('budget__c')->toArray();
            $listProposalBudget->delete();
            $proposal->delete();
            DB::commit();

            $this->hHelperHandleTotalAmount->caseDeleteParent('proposal', $arrBudget);

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
            return redirect()->back()->withErrors(['message' => __('messages.System_Error')])->withInput();
        }
    }

    public function submitApproval(Request $request) {
        try {
            $id = $request->input('id');
            $proposal = $this->mProposal::findOrFail($id);

            $response = $this->hHelperGuzzleService->submitApproval(Auth::user()->accessToken, $proposal->sfid);

            if($response->success == false && $response->statusCode == 400) {
                return redirect()->back()->withErrors(['message' => $response->message])->withInput();
            }

            if($response->success == true) {
                $proposal->status_approve = $this->hHelperConvertDateTime::SUBMIT;
                $proposal->save();
                return redirect('proposal/'. $id);
            }

            return redirect()->back()->withErrors(['message' => __('messages.System_Error')]);

        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- submitApproval - ProposalController');
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

<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Proposal;
use App\Models\Budget;
use DB, Session;
use Illuminate\Support\Facades\Log;

class HelperHandleTotalAmount 
{
  public static function caseCreateDeleteJunction($proposal__c, $budget__c)
  {
    DB::beginTransaction();

    try{

      $proposal = Proposal::where('sfid', $proposal__c)->first();
      if($proposal) {
        $totalAmountProposal = $proposal->proposal_budget->sum('amount__c');
        $proposal->total_amount__c = $totalAmountProposal;
        $proposal->save();
      }
      
      $budget = Budget::where('sfid', $budget__c)->first();
      if($budget) {
        $totalAmountBudget = $budget->proposal_budget->sum('amount__c');
        $budget->total_amount__c = $totalAmountBudget;
        $budget->save();
      }
      
      DB::commit();

    }catch(\Exception $ex) {
      DB::rollback();
      Log::info($ex->getMessage().'- caseCreateDeleteJunction - HelperHandleTotalAmount');
    }   
  }

  public static function caseUpdateJunction($oldPB, $newPB)
  {
    DB::beginTransaction();

    try{

      $proposal = Proposal::where('sfid', $oldPB['proposal__c'])->first();
      if($proposal) {
        $totalAmountProposal = $proposal->proposal_budget->sum('amount__c');
        $proposal->total_amount__c = $totalAmountProposal;
        $proposal->save();
      }

      $budget = Budget::where('sfid', $oldPB['budget__c'])->first();
      if($budget) {
        $totalAmountBudget = $budget->proposal_budget->sum('amount__c');
        $budget->total_amount__c = $totalAmountBudget;
        $budget->save();
      }

      if(isset($newPB['proposal__c'])) {
        $proposalNew = Proposal::where('sfid', $newPB['proposal__c'])->first();
        if($proposalNew) {
          $totalAmountProposal = $proposalNew->proposal_budget->sum('amount__c');
          $proposalNew->total_amount__c = $totalAmountProposal;
          $proposalNew->save();
        }
      }

      if(isset($newPB['budget__c'])) {
        $budgetNew = Budget::where('sfid', $newPB['budget__c'])->first();
        if($budgetNew) {
          $totalAmountBudget = $budgetNew->proposal_budget->sum('amount__c');
          $budgetNew->total_amount__c = $totalAmountBudget;
          $budgetNew->save();
        }
      }
      
      DB::commit();

    }catch(\Exception $ex) {
      DB::rollback();
      Log::info($ex->getMessage().'- caseUpdateJunction - HelperHandleTotalAmount');
    }   
  }

  public static function caseDeleteParent($typeParent, $arrSfId)
  {
    DB::beginTransaction();

    try{

      if($typeParent == 'proposal')
      {
        $listBudget = Budget::whereIn('sfid', $arrSfId)->get();
        foreach ($listBudget as $budget) {
          $totalAmountBudget = $budget->proposal_budget->sum('amount__c');
          $budget->total_amount__c = $totalAmountBudget;
          $budget->save();
        }
      }

      if($typeParent == 'budget') {
        $listProposal = Proposal::whereIn('sfid', $arrSfId)->get();
        foreach ($listProposal as $proposal) {
          $totalAmountProposal = $proposal->proposal_budget->sum('amount__c');
          $proposal->total_amount__c = $totalAmountProposal;
          $proposal->save();
        }
      }
      
      DB::commit();

    }catch(\Exception $ex) {
      DB::rollback();
      Log::info($ex->getMessage().'- caseDeleteParent - HelperHandleTotalAmount');
    }   
  }

}


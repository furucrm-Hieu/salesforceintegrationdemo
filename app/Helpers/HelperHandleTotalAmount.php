<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Proposal;
use App\Models\Budget;
use DB, Session;

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

  public static function caseDeleteParentOrJunction($typeParent)
  {
    DB::beginTransaction();

    try{

      if($typeParent == 'proposal' || $typeParent == 'all')
      {
        $listBudget = Budget::whereNotNull('sfid')->get();
        foreach ($listBudget as $budget) {
          $totalAmountBudget = $budget->proposal_budget->sum('amount__c');
          $budget->total_amount__c = $totalAmountBudget;
          $budget->save();
        }
      }

      if($typeParent == 'budget' || $typeParent == 'all') {
        $listProposal = Proposal::whereNotNull('sfid')->get();
        foreach ($listProposal as $proposal) {
          $totalAmountProposal = $proposal->proposal_budget->sum('amount__c');
          $proposal->total_amount__c = $totalAmountProposal;
          $proposal->save();
        }
      }
      
      DB::commit();

    }catch(\Exception $ex) {
      DB::rollback();
      Log::info($ex->getMessage().'- caseDeleteParentOrJunction - HelperHandleTotalAmount');
    }   
  }

}


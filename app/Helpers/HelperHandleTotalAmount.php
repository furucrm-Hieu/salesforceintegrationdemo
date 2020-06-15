<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Proposal;
use App\Models\Budget;
use App\Models\Expense;
use DB, Session;
use Illuminate\Support\Facades\Log;

class HelperHandleTotalAmount 
{
  public function caseCreateDeleteJunction($proposal__c, $expense__c, $budget__c)
  {
    DB::beginTransaction();

    try{

      if(!empty($proposal__c)) {
        $proposal = Proposal::where('sfid', $proposal__c)->first();
        if($proposal) {
          $totalAmountProposal = $proposal->proposal_budget->sum('amount__c');
          $proposal->total_amount__c = $totalAmountProposal;
          $proposal->save();
        }
      }

      if(!empty($expense__c)) {
        $expense = Expense::where('sfid', $expense__c)->first();
        if($expense) {
          $totalAmountExpense = $expense->expense_budget->sum('amount__c');
          $expense->total_amount__c = $totalAmountExpense;
          $expense->save();
        }
      }
      
      if(!empty($budget__c)) {
        $budget = Budget::where('sfid', $budget__c)->first();
        if($budget) {
          $totalAmountPB = $budget->proposal_budget->sum('amount__c');
          $totalAmountEB = $budget->expense_budget->sum('amount__c');
          $budget->total_amount__c = $totalAmountPB + $totalAmountEB;
          $budget->save();
        }
      }
      
      
      DB::commit();

    }catch(\Exception $ex) {
      DB::rollback();
      Log::info($ex->getMessage().'- caseCreateDeleteJunction - HelperHandleTotalAmount');
    }   
  }

  public function caseUpdateJunction($oldPB, $newPB)
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

  public function caseDeleteParent($typeParent, $arrSfId, $arrSfId1 = null)
  {
    DB::beginTransaction();

    try{

      if($typeParent == 'proposal' || $typeParent == 'expense')
      {
        $listBudget = Budget::whereIn('sfid', $arrSfId)->get();
        foreach ($listBudget as $budget) {
          $totalAmountPB = $budget->proposal_budget->sum('amount__c');
          $totalAmountEB = $budget->expense_budget->sum('amount__c');
          $budget->total_amount__c = $totalAmountPB + $totalAmountEB;
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
        $listExpense = Expense::whereIn('sfid', $arrSfId1)->get();
        foreach ($listExpense as $expense) {
          $totalAmountExpense = $expense->expense_budget->sum('amount__c');
          $expense->total_amount__c = $totalAmountExpense;
          $expense->save();
        }
      }
      
      DB::commit();

    }catch(\Exception $ex) {
      DB::rollback();
      Log::info($ex->getMessage().'- caseDeleteParent - HelperHandleTotalAmount');
    }   
  }

}


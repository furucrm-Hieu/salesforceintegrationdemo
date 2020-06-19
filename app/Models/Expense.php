<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $table = 'expense__c';

    protected $primaryKey = 'id';

    protected $fillable = [
       'name', 'proposed_at__c', 'approved_at__c', 'year__c', 'total_amount__c', 'details__c', 'sfid', 'status_approve', 'type_submit'
    ];

    // public function budget() {
    //     return $this->belongsToMany('App\Models\Budget', 'proposal_budget', 'proposal_id', 'budget_id')->withPivot('proposal_id', 'budget_id', 'amount');
    // }

    public function expense_budget() {
        return $this->hasMany('App\Models\ExpenseBudget', 'expense__c', 'sfid');
    }
}

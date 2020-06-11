<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{   
    // public $timestamps = false;
    
    // protected $table = 'salesforce.proposal__c';
    protected $table = 'proposal__c';

    protected $primaryKey = 'id';

    protected $fillable = [
       'name', 'proposed_at__c', 'approved_at__c', 'year__c', 'total_amount__c', 'details__c', 'sfid', 'status_approve'
    ];

    public function budget() {
        return $this->belongsToMany('App\Models\Budget', 'proposal_budget', 'proposal_id', 'budget_id')->withPivot('proposal_id', 'budget_id', 'amount');
    }

    public function proposal_budget() {
        return $this->hasMany('App\Models\ProposalBudget', 'proposal__c', 'sfid');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalBudget extends Model
{
    // public $timestamps = false;

    // protected $table = 'salesforce.proposal_budget__c';
    protected $table = 'proposal_budget__c';

    protected $primaryKey = 'id';
    
    protected $fillable = [
        'proposal__c', 'budget__c', 'amount__c', 'sfid', 'status_approve'
    ];

    public function budget() {
        return $this->hasOne('App\Models\Budget', 'sfid', 'budget__c');
    }

    public function proposal() {
        return $this->hasOne('App\Models\Proposal', 'sfid', 'proposal__c');
    }
}

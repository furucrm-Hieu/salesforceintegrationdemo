<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    // protected $table = 'salesforce.budget__c';
    protected $table = 'budget__c';
    
    protected $primaryKey = 'id';

    protected $fillable = [
        'id', 'name', 'year__c', 'total_amount__c', 'sfid',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    
    public function proposal() {
        return $this->belongsToMany('App\Models\Proposal', 'salesforce.proposal_budget__c', 'budget__c', 'proposal__c')->withPivot('id', 'budget__c', 'proposal__c', 'amount__c');
    }

    public function proposal_budget() {
        return $this->hasMany('App\Models\ProposalBudget', 'budget__c', 'sfid');
    }
}

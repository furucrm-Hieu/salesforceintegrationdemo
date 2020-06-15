<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseBudget extends Model
{
    protected $table = 'expense_budget__c';

    protected $primaryKey = 'id';
    
    protected $fillable = [
        'expense__c', 'budget__c', 'amount__c', 'sfid', 'status_approve'
    ];

    public function budget() {
        return $this->hasOne('App\Models\Budget', 'sfid', 'budget__c');
    }

    public function expense() {
        return $this->hasOne('App\Models\Expense', 'sfid', 'expense__c');
    }
}

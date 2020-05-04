<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupProposalBudget extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    // public function up()
    // {
    //     Schema::create('proposal__c', function (Blueprint $table) {
    //         $table->increments('id');
    //         $table->string('name', 80);
    //         $table->string('year__c', 4);
    //         $table->timestamp('proposed_at__c');
    //         $table->timestamp('approved_at__c');
    //         $table->longText('detail__c');
    //         $table->double('total_amount__c', 16, 2);
    //         $table->string('sfid', 20);
    //         $table->timestamps();
    //     });

    //     Schema::create('budget__c', function (Blueprint $table) {
    //         $table->increments('id');
    //         $table->string('name', 80);
    //         $table->string('year__c', 4);
    //         $table->double('total_amount__c', 16, 2);
    //         $table->string('sfid', 20);
    //         $table->timestamps();
    //     });

    //     Schema::create('proposal_budget__c', function (Blueprint $table) {
    //         $table->increments('id');
    //         $table->integer('proposal_id__c');
    //         $table->integer('budget_id__c');
    //         $table->double('amount__c', 16, 2);
    //         $table->timestamps();
    //     });

    // }

    // /**
    //  * Reverse the migrations.
    //  *
    //  * @return void
    //  */
    // public function down()
    // {
    //     Schema::dropIfExists('proposal');
    //     Schema::dropIfExists('budget');
    //     Schema::dropIfExists('proposal_budget');
    // }
}

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
    public function up()
    {
        Schema::create('api_connect', function (Blueprint $table) {
            $table->increments('id');
            $table->string('accessToken');
            $table->string('refreshToken');
            $table->boolean('status');
            $table->timestamps();
        });

        Schema::create('proposal__c', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 80);
            $table->string('year__c', 4);
            $table->dateTime('proposed_at__c');
            $table->dateTime('approved_at__c');
            $table->longText('details__c');
            $table->double('total_amount__c', 16, 2);
            $table->string('sfid', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('budget__c', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 80);
            $table->string('year__c', 4);
            $table->double('total_amount__c', 16, 2);
            $table->string('sfid', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('proposal_budget__c', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('proposal__c');
            $table->integer('budget__c');
            $table->double('amount__c', 16, 2);
            $table->string('sfid', 20)->nullable();
            $table->timestamps();
        });

    }

    // /**
    //  * Reverse the migrations.
    //  *
    //  * @return void
    //  */
    public function down()
    {
        Schema::dropIfExists('api_connect');
        Schema::dropIfExists('proposal__c');
        Schema::dropIfExists('budget__c');
        Schema::dropIfExists('proposal_budget__c');
    }
}

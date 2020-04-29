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
        Schema::create('proposal__c', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 80);
            $table->integer('year__c');
            $table->dateTime('proposed_at__c');
            $table->dateTime('approved_at__c');
            $table->longText('detail__c');
            $table->decimal('total_amount__c', 16, 2);
            $table->string('external_id__c',80);
            $table->timestamps();
        });

        Schema::create('budget__c', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 80);
            $table->integer('year__c');
            $table->decimal('total_amount__c', 16, 2);
            $table->string('external_id__c',80);
            $table->timestamps();
        });

        Schema::create('proposal_budget__c', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('proposal_id__c');
            $table->unsignedBigInteger('budget_id__c');
            $table->decimal('amount__c', 16, 2);
            $table->timestamps();
            
            $table->foreign('proposal_id__c')->references('id')
                    ->on('proposal__c')->onDelete('cascade');
            $table->foreign('budget_id__c')->references('id')
                    ->on('budget__c')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proposal');
        Schema::dropIfExists('budget');
        Schema::dropIfExists('proposal_budget');
    }
}

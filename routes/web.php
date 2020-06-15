<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['checkAuth']], function() {

    Route::resource('/proposal', 'ProposalController');
    Route::post('/proposal-submit-approval', 'ProposalController@submitApproval');

    Route::resource('/budget', 'BudgetController');
    Route::post('/budget-submit-approval', 'BudgetController@submitApproval');

    Route::resource('/proposal-budget', 'ProposalBudgetController');
    Route::post('/junctionPB-submit-approval', 'ProposalBudgetController@submitApproval');
    Route::get('/junctionPB/{id}', 'ProposalBudgetController@createJunction');

    Route::resource('/expense', 'ExpenseController');
    Route::post('/expense-submit-approval', 'ExpenseController@submitApproval');

    Route::resource('/expense-budget', 'ExpenseBudgetController');
    Route::post('/junctionEB-submit-approval', 'ExpenseBudgetController@submitApproval');
    Route::get('/junctionEB/{id}', 'ExpenseBudgetController@createJunction');

    Route::get('lang/{locale}', 'AuthController@changeLocalization');
    Route::get('/profile', 'AuthController@userProfile')->name('profile');
    Route::prefix('oauth2') ->group(function() {
        Route::get('/authSalesforce', 'ApiController@authSalesforce')->name('authSalesforce');
        Route::get('/callback', 'ApiController@callback')->name('callback');
        // Route::get('/refreshToken', 'ApiController@refreshToken')->name('refreshToken');
    });
});
Route::get('', 'AuthController@getLogin');
Route::post('post-login', 'AuthController@postLogin');
Route::post('post-logout', 'AuthController@postLogout');

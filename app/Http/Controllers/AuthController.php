<?php

namespace App\Http\Controllers;
use Validator,Redirect,Response;
Use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Session;
use App;
use App\Models\ApiConnect;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function getLogin()
    {
        if(Auth::check()){
            return redirect('proposal');
        }

        return view('auth.login');
    }

    public function postLogin(Request $request)
    {

        request()->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            return redirect('proposal');
        }
        return redirect('/')->with('error', __('messages.AlertLogin'));
    }

    public function postLogout() {
        Auth::logout();
        return redirect('/');
    }

    public function changeLocalization($locale) {
        App::setLocale($locale);
        session()->put('locale', $locale);
        return response()->json(['success' => true]);
    }

    public function userProfile() {
        $uri = config('authenticate.api_uri');
        $user = User::findorFail(Auth::user()->id);
        $api = ApiConnect::latest()->first();
        try {
            DB::beginTransaction();
            if(isset($api)) {
                $reponse = Http::withHeaders([
                    'Authorization' => 'Bearer '.$api->accessToken,
                ])->get($uri.'/Proposal__c');
                if($reponse->status() == 401) {
                    $api->expired = true;
                    $api->save();
                }
            }
            DB::commit();
        }catch(\Exception $ex) {
            Log::info($ex->getMessage().'- userProfile - AuthController');
            DB::rollback();
        }
        return view('user.profile', compact('api', 'user'));
    }
}

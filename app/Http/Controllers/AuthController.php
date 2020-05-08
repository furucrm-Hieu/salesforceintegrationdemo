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
        if(isset($api)) {
            $reponse = Http::withHeaders([
                'Authorization' => 'Bearer '.$api->accessToken,
            ])->get($uri.'/services/data/v48.0/sobjects/Proposal__c');
            if($reponse->status() != 200) {
                $api->expried = true;
                $api->save();
            }
        }
        return view('user.profile', compact('api', 'user'));
    }
}

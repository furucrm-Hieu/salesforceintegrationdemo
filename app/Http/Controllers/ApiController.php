<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\HelperAuthenticateSalesforce as AuthenSalesforce;
use App\Models\ApiConnect;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Auth;
use function GuzzleHttp\json_decode;

class ApiController extends Controller
{
    private $authenSalesforce;
    private $apiConnect;

    public function __construct(AuthenSalesforce $_auth, ApiConnect $_api)
    {
        $this->authenSalesforce = $_auth;
        $this->apiConnect = $_api;
    }

    public function authSalesforce() {
        try {
            $api = $this->apiConnect::latest()->first();
            if(isset($api)) {
                $expired = (bool) $api->expired;
                $api->delete();
                if($expired) {
                    return $this->authenSalesforce->getCode();
                }
            }
            else {
                return $this->authenSalesforce->getCode();
            }
        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- callback - ApiController');
        }
        return redirect()->back();
    }

    public function refreshToken() {
        try {
            $api = $this->apiConnect::latest()->first();
            $authenSalesforce = new AuthenSalesforce();
            $token = json_decode($authenSalesforce->refreshToken($api->refreshToken));
            $api->fill([
                'accessToken' => $token->access_token,
                'expired' => false
            ]);
            $api->save();
            return redirect()->back();
        } catch (\Exception $ex) {
            if($ex instanceof RequestException) {
                if(strpos($ex->response->body() , 'expired access/refresh token')) return $this->authSalesforce();
            }
            else {
                Log::info($ex->getMessage().'- callback - ApiController');
            }
            return redirect()->back()->withErrors('error', $ex->getMessage());
        }
    }

    public function callback(Request $request) {
        try{
            $token = json_decode($this->authenSalesforce->getToken($request->code));
            $this->apiConnect::create([
                'accessToken' => $token->access_token,
                'refreshToken' => $token->refresh_token
            ]);
        }
        catch(\Exception $ex) {
            Log::info($ex->getMessage().'- callback - ApiController');
        }
        return redirect()->route('profile', ['user' => Auth::user()->id]);
    }
}

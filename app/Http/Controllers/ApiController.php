<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\HelperAuthenticateSalesforce as AuthenSalesforce;
use App\Models\ApiConnect;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function callback(Request $request) {
        try{
            $token = json_decode($this->authenSalesforce->getToken($request->code));
            if(isset($token) && $token->access_token) {
                $this->apiConnect::create([
                    'accessToken' => $token->access_token,
                    'refreshToken' => $token->refresh_token
                ]);
            }
        }
        catch(\Exception $ex) {
            Log::info($ex->getMessage().'- callback - ApiController');
        }
        return redirect()->route('profile');
    }
}

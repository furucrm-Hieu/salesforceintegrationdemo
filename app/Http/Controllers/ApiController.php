<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\HelperAuthenticateSalesforce as AuthenSalesforce;
use App\Models\ApiConnect;
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

    public function authSalesforce(Request $request) {
        try {
            $api = $this->apiConnect::latest()->first();;
            if(isset($api)) {
                $api->status = $api->status == 'Synced' ? 'Disconnected' : 'Synced';
                $api->save();
            }
            else {
                return $this->authenSalesforce->getCode();
            }
        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- callback - ApiController');
        }
        return redirect()->back();
    }

    public function refreshToken($refresh_code) {
        $authenSalesforce = new AuthenSalesforce();
        $token = $authenSalesforce->refreshToken($refresh_code);
        $api = $this->apiConnect::latest()->fill(['accessToken' => $token]);
        $api->save();
    }

    public function callback(Request $request) {
        try{
            $token = json_decode($this->authenSalesforce->getToken($request->code));
            $this->apiConnect::create([
                'accessToken' => $token->access_token,
                'refreshToken' => $token->refresh_token,
                'status' =>  'Synced'
            ]);
            //dd($token);
        }
        catch(\Exception $ex) {
            //dd($ex);
            Log::info($ex->getMessage().'- callback - ApiController');
        }
        return redirect()->route('user.show', ['user' => Auth::user()->id]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\HelperAuthenticateSalesforce as AuthenSalesforce;
use App\Helpers\HelperGuzzleService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use DB;

use function GuzzleHttp\json_decode;

class ApiController extends Controller
{
    private $authenSalesforce;
    private $apiConnect;
    private $helperService;

    public function __construct(AuthenSalesforce $_auth, User $_api, HelperGuzzleService $helperService)
    {
        $this->authenSalesforce = $_auth;
        $this->apiConnect = $_api;
        $this->helperService = $helperService;
    }

    public function authSalesforce() {
        try {
            $user = $this->apiConnect::findOrFail(Auth::user()->id);
            if($user->accessToken) {
                DB::beginTransaction();
                $user->update([
                    'accessToken' => null,
                    'refreshToken' => null,
                    'userId' => null,
                    'roleName' => null,
                    'userNameSF' => null
                ]);
                DB::commit();
            }
            else {
                return $this->authenSalesforce->getCode();
            }
        } catch (\Exception $ex) {
            Log::info($ex->getMessage().'- callback - ApiController');
            DB::rollback();
        }
        return redirect()->back();
    }

    public function callback(Request $request) {
        try{
            $token = json_decode($this->authenSalesforce->getToken($request->code));
            if(isset($token) && $token->access_token) {
                $id = explode('/', $token->id);
                $userId = last($id);
                $userSF = $this->helperService->getRoleUser($token->access_token, $userId);

                DB::beginTransaction();
                $this->apiConnect::findOrFail(Auth::user()->id)->update([
                    'accessToken' => $token->access_token,
                    'refreshToken' => $token->refresh_token,
                    'userNameSF' => $userSF->Username,
                    'userId' => $userId,
                    'roleName' => $userSF->UserRole ?? 'User'
                ]);
                DB::commit();
            }
        }
        catch(\Exception $ex) {
            Log::info($ex->getMessage().'- callback - ApiController');
            DB::rollback();
        }
        return redirect()->route('profile');
    }
}

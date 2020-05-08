<?php

namespace App\Helpers;

use Carbon\Carbon;
use DB, Session;
use App\Models\ApiConnect;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\{Client, RequestOptions};
use Illuminate\Support\Facades\Http;

class HelperGuzzleService 
{

  public static function guzzlePost($url, $token, $param_data) {
    try {

      $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$token
      ])->post($url, $param_data);

      if($response->status() == 401) {
        return '{"success" : false}';
      }

      $response = $response->getBody()->getContents();
      return $response;

    } catch (\Exception $ex) {
      Log::info($ex->getMessage().'- guzzlePost - HelperGuzzleService');
    }
  }

  public static function guzzleUpdate($url, $token, $param_data) {
    try {

      $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$token
      ])->patch($url, $param_data);

      if($response->status() == 401) {
        return '{"success" : false}';
      }

    } catch (\Exception $ex) {
      Log::info($ex->getMessage().'- guzzleUpdate - HelperGuzzleService');
    }
  }

  public static function guzzleDelete($url, $token) {
    try {

      $response = Http::withHeaders([
        'Authorization' => 'Bearer '.$token
      ])->delete($url);

      if($response->status() == 401) {
        return '{"success" : false}';
      }

    } catch (\Exception $ex) {
      Log::info($ex->getMessage().'- guzzleDelete - HelperGuzzleService');
    }
  }

  public static function refreshToken($code) {

    try {
      $response = HTTP::asForm()->post(config('authenticate.uri').'/services/oauth2/token', [
        'client_id' => config('authenticate.client_id'),
        'client_secret' => config('authenticate.client_secret'),
        'grant_type' => 'refresh_token',
        'redirect_uri' => config('authenticate.redirect_uri'),
        'refresh_token' => $code
      ]);

      $token = json_decode($response->body());

      DB::beginTransaction();
      $api = ApiConnect::latest()->first();
      $api->fill(['accessToken' => $token->access_token]);
      $api->save();
      DB::commit();

      return '{"success" : true, "access_token" : "'.$token->access_token.'"}';
    } catch (\Exception $ex) {
      DB::rollback();
      return '{"success" : false}';
    }
  }
  
}

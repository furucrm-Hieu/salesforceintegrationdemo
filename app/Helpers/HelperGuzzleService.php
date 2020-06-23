<?php

namespace App\Helpers;

use Carbon\Carbon;
use DB, Session;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\{Client, RequestOptions};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class HelperGuzzleService
{

  const LINK_SF = "https://eap-prototype-dev-ed.my.salesforce.com/services/data/";

  public static function guzzlePost($url, $token, $param_data) {
    try {

      $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$token
      ])->post($url, $param_data);

      if($response->status() == 401) {
        return json_decode('{"success" : false, "statusCode" : 401}');
      }

      if($response->status() == 400) {
        $data = json_decode($response->getBody()->getContents());
        return json_decode('{"success" : false, "statusCode" : 400, "message" : "'.$data[0]->message.'"}');
      }

      if($response->status() == 201) {
        $response = $response->getBody()->getContents();
        return json_decode($response);
      }

      return json_decode('{"success" : false, "statusCode" : 500}');

    } catch (\Exception $ex) {
      return json_decode('{"success" : false, "statusCode" : 500}');
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
        return json_decode('{"success" : false, "statusCode" : 401}');
      }

      if($response->status() == 400) {
        $data = json_decode($response->getBody()->getContents());
        return json_decode('{"success" : false, "statusCode" : 400, "message" : "'.$data[0]->message.'"}');
      }

      if($response->status() == 204) {
        return json_decode('{"success" : true}');
      }

      return json_decode('{"success" : false, "statusCode" : 500}');

    } catch (\Exception $ex) {
      return json_decode('{"success" : false, "statusCode" : 500}');
      Log::info($ex->getMessage().'- guzzleUpdate - HelperGuzzleService');
    }
  }

  public static function guzzleDelete($url, $token) {
    try {

      $response = Http::withHeaders([
        'Authorization' => 'Bearer '.$token
      ])->delete($url);

      if($response->status() == 401) {
        return json_decode('{"success" : false, "statusCode" : 401}');
      }

      if($response->status() == 204) {
        return json_decode('{"success" : true}');
      }

      return json_decode('{"success" : false, "statusCode" : 500}');

    } catch (\Exception $ex) {
      return json_decode('{"success" : false, "statusCode" : 500}');
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
      $api = User::findOrFail(Auth::user()->id);
      $api->fill(['accessToken' => $token->access_token]);
      $api->save();
      DB::commit();

      return json_decode('{"success" : true, "access_token" : "'.$token->access_token.'"}');
    } catch (\Exception $ex) {
      DB::rollback();
      return json_decode('{"success" : false}');
    }
  }

  public function guzzleGetApproval($token, $code) {

    $url = $this::LINK_SF."v36.0/query/?q=SELECT+Id,(SELECT+Id,OriginalActor.Name,ProcessNode.Name,SystemModstamp,Comments,StepStatus+FROM+StepsAndWorkitems+ORDER+BY+CreatedDate+DESC,+Id+DESC)+FROM+ProcessInstance+WHERE+TargetObjectId+=+'".$code."'+ORDER+BY+CreatedDate+DESC";

    $response = Http::withHeaders([
      'Authorization' => 'Bearer '.$token,
      'Content-Type' => 'application/json',
    ])->get($url);

    if($response->status() == 200) {
      $data = json_decode($response->body());
      return $this->convertDataApprovalProcess($data);
    }

    return [];
  }

  public function submitApproval($token, $param_data) {
    try {

      $link = "https://eap-prototype-dev-ed.my.salesforce.com/services/data/";

      $url = $this::LINK_SF."v30.0/process/approvals";

      $dataSubmit = (Object) [
        "actionType" => "Submit",
        "contextId" => $param_data,
        "comments" => "Submit request",
      ];

      $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$token
      ])->post($url, [
        "requests" => [$dataSubmit]
      ]);

      if($response->status() == 400) {
        $data = json_decode($response->getBody()->getContents());
        return json_decode('{"success" : false, "statusCode" : 400, "message" : "'.$data[0]->message.'"}');
      }

      if($response->status() == 200) {
        return json_decode('{"success" : true}');
      }
      return json_decode('{"success" : false}');

    } catch (\Exception $ex) {
      return json_decode('{"success" : false}');
      Log::info($ex->getMessage().'- guzzlePost - HelperGuzzleService');
    }
  }

  public function convertDataApprovalProcess($data) {
    $arrValue = [];

    if(count($data->records) > 0)
    {
      foreach ($data->records as $value) {

        $temp1 = $value->StepsAndWorkitems->records;

        foreach ($temp1 as $key => $value1) {

          $arrData = [
          "StepName" => empty($value1->ProcessNode->Name) ? 'Request Submitted' : $value1->ProcessNode->Name,
          "Date" => $this->convertDateTimeApproval($value1->SystemModstamp),
          "Status" => ($value1->StepStatus == 'Started') ? 'Submitted' : $value1->StepStatus,
          "AssignedTo" => $value1->OriginalActor->Name,
          ];

          array_push($arrValue, $arrData);

        }
      }
    }

    return $arrValue;
  }

  public function getRoleUser($token, $code){

    $url = $this::LINK_SF."v36.0/query/?q=SELECT+Username,+UserRole.Name+FROM+User+WHERE+Id+=+'".$code."'";

    $response = Http::withHeaders([
      'Authorization' => 'Bearer '.$token,
      'Content-Type' => 'application/json',
    ])->get($url);

    if($response->status() == 200) {
      $data = json_decode($response->getBody()->getContents());
      return $data->records[0];
      //return empty($roleName) ? 'User' : $roleName->Name;
    }

    return 'User';
  }

  public function convertDateTimeApproval($datetime) {
    $datetime = str_replace("T"," ", $datetime);
    $datetime = str_replace(".000+0000","", $datetime);
    $dateTimeJP = Carbon::createFromFormat('Y-m-d H:i:s', $datetime);
    $dateTimeUTC = $dateTimeJP->addHours(9);
    return $dateTimeUTC->toDateTimeString();
  }

  public function getFieldManager($token, $code){

    $url = $this::LINK_SF."v36.0/query/?q=SELECT+Username,+Manager.Name+FROM+User+WHERE+Id+=+'".$code."'";

    $response = Http::withHeaders([
      'Authorization' => 'Bearer '.$token,
      'Content-Type' => 'application/json',
    ])->get($url);

    if($response->status() == 200) {
      $data = json_decode($response->getBody()->getContents());
      $userManager = $data->records[0]->Manager;
      return empty($userManager) ? '' : $userManager->Name;
    }

    return '';
  }

  public function getFieldText2($token, $object){

    $url = $this::LINK_SF."v36.0/query/?q=SELECT+Description,DeveloperName,Id,Name+FROM+ProcessNode+WHERE+ProcessDefinition.State+=+'Active'+and+DeveloperName+<>+'Supervisor'+and+ProcessDefinition.TableEnumOrId+=+'".$object."'";

    $response = Http::withHeaders([
      'Authorization' => 'Bearer '.$token,
      'Content-Type' => 'application/json',
    ])->get($url);

    if($response->status() == 200) {
      $data = json_decode($response->getBody()->getContents());
      return $this->convertDataInfoApproval($data);
    }

    return [];
  }

  public function convertDataInfoApproval($data) {
    $arrValue = [];

    if(count($data->records) > 0)
    {
      foreach ($data->records as $value) {
        $arrData = [
          "Description" => $value->Description,
          "DeveloperName" => $value->DeveloperName,
          "Name" => $value->Name,
        ];
        array_push($arrValue, $arrData);
      }
    }
    
    return $arrValue;
  }

}


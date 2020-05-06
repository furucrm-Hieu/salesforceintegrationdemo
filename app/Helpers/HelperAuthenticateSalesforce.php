<?php
namespace app\Helpers;
use Exception;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Http;
use config;

class HelperAuthenticateSalesforce{

    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $uri;

    public function __construct()
    {
        $this->client_id = config('authenticate.client_id');
        $this->client_secret = config('authenticate.client_secret');
        $this->redirect_uri = config('authenticate.redirect_uri');
        $this->uri = config('authenticate.uri');
    }

    private function callGetCode() {
        return Redirect::to(
            $this->uri.'/services/oauth2/authorize?client_id='.$this->client_id.'&redirect_uri='.$this->redirect_uri.'&response_type=code'
        );
    }

    public function getCode() {
        return $this->callGetCode();
    }

    public function getToken($code) {
        return $this->callAuth2getToken($code, 'authorization_code');
    }

    public function refreshToken($code) {
        return $this->callAuth2getToken($code, 'refresh_token');
    }

    private function callAuth2getToken($code, $type) {
        $response = HTTP::asForm()->post($this->uri.'/services/oauth2/token', [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => $type,
            'redirect_uri' => $this->redirect_uri,
            'code' => $code
        ]);
        $response->throw();
        return $response->body();
    }
}
?>

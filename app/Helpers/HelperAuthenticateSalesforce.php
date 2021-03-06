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
        return $this->callAuth2getToken($code);
    }

    public function refreshToken($code) {
        return $this->callAuth2RefreshToken($code);
    }

    private function callAuth2getToken($code) {
        $response = HTTP::asForm()->post($this->uri.'/services/oauth2/token', [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirect_uri,
            'code' => $code
        ]);
        $response->throw();
        return $response->body();
    }

    private function callAuth2RefreshToken($code) {
        $response = HTTP::asForm()->post($this->uri.'/services/oauth2/token', [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'refresh_token',
            'redirect_uri' => $this->redirect_uri,
            'refresh_token' => $code
        ]);
        $response->throw();
        return $response->body();
    }
}
?>

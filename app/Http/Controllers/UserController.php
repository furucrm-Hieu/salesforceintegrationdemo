<?php

namespace App\Http\Controllers;

use App\Models\ApiConnect;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    private $apiConnect;

    public function __construct(ApiConnect $_apiConnect)
    {
        $this->apiConnect = $_apiConnect;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $api = $this->apiConnect->latest()->first();

        //  $client = Http::post('https://eap-prototype-dev-ed.my.salesforce.com/services/data/v48.0/sobjects/account/describe', [
        //      'Authorization' => 'Bearer '.$api->accessToken
        //  ]);
        // dd(json_decode($client->body()));
        // $curl = curl_init();
        // curl_setopt_array($curl, array(
        //     CURLOPT_RETURNTRANSFER => 1,
        //     CURLOPT_URL => 'https://eap-prototype-dev-ed.my.salesforce.com/services/data/v48.0/sobjects/account/describe',
        //         CURLOPT_POST => 1,
        //         CURLOPT_SSL_VERIFYPEER => false, //Bỏ kiểm SSL
        //         CURLOPT_POSTFIELDS => http_build_query(array(
        //             'Authorization' => 'Bearer 00D2w000003yx07!ARAAQNsxMdxRde5wsyefOtaw7c2H4hBSx5E_rVZPXSkfggRYKTDaOQ0SC2FNa4scnsoLJMNBnLMf9YzuJhEU1TRVER2wys4A'
        //         ))
        //     ));
        // $res = json_decode(curl_exec($curl));


        // dd($curl);
        // curl_close($curl);
        return view('user.profile', compact('status', 'api'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

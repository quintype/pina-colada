<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\QuintypeController;

class ProxyController extends QuintypeController
{
    public function __construct()
    {
        $this->host = config("quintype.api-host");
    }

    function getRoute($request) {
        return $this->host . "/" . $request->path();
    }

    public function proxyGet(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $client->request("GET", "http://sketches.quintype.com/qlitics.js");
        $res = $client->request('GET', $this->getRoute($request));
        return response($res->getBody(), $res->getStatusCode())->withHeaders($res->getHeaders());
    }

    public function proxyPost(Request $request, Response $response)
    {
        return $this->getRoute($request);
    }
}
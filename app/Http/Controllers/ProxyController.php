<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class ProxyController extends BaseController
{
    public function __construct()
    {
        $this->host = config("quintype.api-host");
    }

    function getRoute($request) {
        $baseUrl = $this->host . "/" . $request->path();
        $queryString = $request->getQueryString();

        if($queryString && $queryString != "")
            return $baseUrl . '?' . $queryString;
        else
            return $baseUrl;
    }

    public function proxyGet(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', $this->getRoute($request));
        return response($res->getBody(), $res->getStatusCode())->withHeaders($res->getHeaders());
    }

    public function proxyPost(Request $request, Response $response)
    {
        return $this->getRoute($request);
    }
}
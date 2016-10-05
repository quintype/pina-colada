<?php

namespace App\Api;

use ArrayObject;
use GuzzleHttp\Client;
use Log;

class MenuItem extends ArrayObject
{
    public function __construct($hash, $config) {
        parent::__construct($hash);
        $this->config = $config;
    }

    public function title() {
        return $this["title"];
    }

    public function url() {
        switch($this["item-type"]) {
        case "section": return "/section/" . $this["section-slug"];
        default: return "#";
        }
    }
}

class Config extends ArrayObject
{
    public function menuItems() {
        return array_map(function($menu) {
            return new MenuItem($menu, $this);
        }, $this["layout"]["menu"]);
    }
}

class Story extends ArrayObject
{

}

class Bulk
{
    public function __construct() {
        $this->requests = [];
    }

    public function addRequest($name, $request) {
        $this->requests[$name] = $request;
        return $this;
    }

    public function execute($client) {
        $requests = [];
        foreach($this->requests as $key => $value) {
            $requests[$key] = $value->toBulkRequest();
        }
        $apiResponse = $client->postBulk($requests);
        $responses = [];
        foreach($this->requests as $key => $value) {
            $responses[$key] = $value->fromBulkResponse($apiResponse[$key]);
        }
        $this->responses = $responses;
    }

    public function getResponse($name) {
        return $this->responses[$name];
    }
}

class StoriesRequest
{
    public function __construct($storyGroup) {
        $this->params = ["story-group" => $storyGroup, "_type" => "stories"];
    }

    public function addParams($params) {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    public function toBulkRequest() {
        return $this->params;
    }

    public function fromBulkResponse($response) {
        return array_map(function ($s) {
            return new Story($s);
        }, $response["stories"]);
    }
}

class QuintypeClient
{
    public function __construct($apiHost) {
        $this->client = new Client([
            "base_uri" => $apiHost
        ]);
    }

    public function getResponse($query, $params = null) {
    	return json_decode($this->client->get($query, ['query' => $params])->getBody(), true);
    }

    public function postResponse($url, $body) {
        return json_decode($this->client->post($url, ['json' => $body])->getBody(), true);
    }

    public function config() {
        return new Config($this->getResponse("/api/v1/config"));
    }

    public function stories($params = null) {
        return array_map(function ($s) {
            return new Story($s);
        }, $this->getResponse("/api/v1/stories", $params)["stories"]);
    }

    public function postBulk($requests) {
        return $this->postResponse("/api/v1/bulk", ["requests" => $requests])["results"];
    }
}

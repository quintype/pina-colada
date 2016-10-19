<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Api;
use Meta;
use Seo;

class QuintypeController extends Controller
{
  public function __construct(){
    $this->client = new Api(config("quintype.api-host"));
    $this->config = array_merge($this->client->config(), config('quintype'));
    $this->meta = new Meta();
    $this->seo = new Seo($this->config);
  }

  public function toView($args) {
    return array_merge([
      "site_url" => url('/'),
      "config" => $this->config,
      "menuItems" => $this->client->menuItems($this->config["layout"]["menu"]),
      "loadMoreFields" => config("quintype.loadMoreFields")
    ], $args);
  }

  protected function getKeys($groupKeys, $stories) {
    $keys = [];
    $publisherId = $this->config["publisher-id"];
    foreach($groupKeys as $x) {
      array_push($keys, "q/$publisherId/$x");
    }
    foreach($stories as $x) {
      array_push($keys, "s/$publisherId/" . preg_replace('/-.*/', "", $x["id"]));
    }
    return join(" ", $keys);
  }
}

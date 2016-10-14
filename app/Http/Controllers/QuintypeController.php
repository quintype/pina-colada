<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Api;
use Meta;
use Seo;

class QuintypeController extends Controller
{
  public function __construct()
  {
    $this->client = new Api(config("quintype.api-host"));
    $this->config = array_merge($this->client->config(), config('quintype'));
    $this->meta = new Meta();
    $this->seo = new Seo($this->config);
  }

  public function toView($args) {
    return array_merge([
      "config" => $this->config,
      "menuItems" => $this->client->menuItems($this->config["layout"]["menu"]),
      "loadMoreFields" => config("quintype.loadMoreFields")
    ], $args);
  }
}

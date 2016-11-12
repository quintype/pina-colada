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
      "client" => $this->client,
      "nestedMenuItems" => $this->client->prepareNestedMenu($this->config["layout"]["menu"]),
    ], $args);
  }
}

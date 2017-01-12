<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Api;
use Meta;
use Seo;
use Caching;

class QuintypeController extends Controller
{
  public function __construct(){
    $this->client = new Api(config("quintype.api-host"));
    $this->config = array_merge($this->client->config(), config('quintype'));
    $this->allStacks = $this->config["layout"]["stacks"];
    $this->meta = new Meta();
    $this->seo = new Seo($this->config);
    $this->caching = new Caching();
    $this->defaultCacheParams = [
        'publisherId' => $this->config['publisher-id'],
        'cdnTTLs' => [
          'max-age' => 3 * 60,
          'stale-while-revalidate' => 5 * 60,
          'stale-if-error' => 4 * 60 * 60,
        ],
        'browserTTLs' => [
          'max-age' => 60,
        ],
    ];
  }

  public function toView($args) {
    return array_merge([
      "config" => $this->config,
      "site_url" => url('/'),
      "client" => $this->client,
      "nestedMenuItems" => $this->client->prepareNestedMenu($this->config["layout"]["menu"]),
    ], $args);
  }

  protected function pageNotFound(){
    return response()->view('errors/404', $this->toView([]), 404);
  }
}

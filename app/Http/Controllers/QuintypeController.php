<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Api;

class QuintypeController extends Controller
{
    public function __construct()
    {
        $this->client = new Api(config("quintype.api-host"));
        $this->config = $this->client->config();
        $this->stacks = $this->config["layout"]["stacks"];
        $this->fields = "id,headline,slug,url,hero-image-s3-key,hero-image-metadata,first-published-at,last-published-at,alternative,published-at,author-name,author-id,sections,story-template,summary,metadata,hero-image-attribution,cards,subheadline,authors";
        $this->loadMoreFields = "sections,hero-image-s3-key,headline,author-name,summary,first-published-at,slug";
    }

    public function toView($args) {
        return array_merge([
            "config" => $this->config,
            "menuItems" => $this->client->menuItems($this->config["layout"]["menu"]),
            "loadMoreFields" => $this->loadMoreFields
        ], $args);
    }
}

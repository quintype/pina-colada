<?php

namespace App\Http\Controllers;

use Log;
use App\Http\Controllers\QuintypeController;
use App\Api\Bulk;
use App\Api\Config;
use App\Api\StoriesRequest;
use Meta;
use Quintype\Seo;

class HomeController extends QuintypeController
{
	public function __construct(){
        parent::__construct();
        $this->meta = new Meta;
        $this->config = $this->client->config();

    }

    public function index()
    {
        $bulk = new Bulk();
        $bulk->addRequest('top_stories', (new StoriesRequest('top')));
        $bulk->addRequest('politics', (new StoriesRequest('top'))->addParams(["section" => "Politics"]));
        $bulk->execute($this->client);

        // Setting Seo meta tags
        $page = ["type" => "home"];
        $home = new Seo\Home(array_merge($this->config, config('quintype')), $page["type"]);
        $this->meta->set($home->tags());

        return view('home', $this->toView([
        	"stories" => $bulk->getResponse("top_stories"),
        	"page" => $page,
            "meta" => $this->meta]));
    }
}
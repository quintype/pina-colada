<?php

namespace App\Http\Controllers;

use Log;
use App\Http\Controllers\QuintypeController;

use Quintype\Api\Bulk;
use Quintype\Api\StoriesRequest;

class HomeController extends QuintypeController{

    public function index(){

      $this->client->addBulkRequest("top_stories", (new StoriesRequest("top")));
      $this->client->addBulkRequest("politics", (new StoriesRequest("top"))->addParams(["section" => "Politics"]));
      $this->client->executeBulk($this->client);

      $top_stories = $this->client->getBulkResponse("top_stories");
      $politics = $this->client->getBulkResponse("politics");

      return view("home", $this->toView([
          "stories" => $top_stories,
          "politics" => $politics
        ])
      );
    }

    public function story($category, $y, $m, $d, $slug) {
      $story = $this->client->story(["slug"=> $slug]);//Get the story details.
      $relatedStories = $this->client->storyRelated($story["id"], "related-stories");//Get all the stories related to this story.
      $comments = $this->client->storyRelated($story["id"], "comments");//Get all the comments for this story.

      return view("story", $this->toView([
          "story" => $story,
          "relatedStories" => $relatedStories,
          "comments" => $comments
        ])
      );
    }

    public function section($section) {
      $sections = $this->config["sections"];//Get all sections.
      $cur_section =  $sections[array_search($section, array_column($sections, "slug"), true)];//Get details of the selected section.

      $params = [
          "story-group" => "top",
          "section" => $cur_section["name"],
          "limit" => 8,
          "fields" => $this->fields
      ];
      $stories = $this->client->stories($params);

      return view("section", $this->toView([
          "stories" => $stories,
          "sectionName" => $cur_section["name"]
        ])
      );
    }

    public function author($authorId) {
      $authorDetails = $this->client->getAuthor($authorId);
      $params =[
            "author-id" => $authorId,
            "sort" => "latest-published",
            "limit" => 4,
            "fields" => $this->fields
        ];
      $authorStories = $this->client->search($params);

      return view("author", $this->toView([
          "authorDetails" => $authorDetails,
          "authorStories" => $authorStories
        ])
      );
    }

    public function tag($tag){
      $params =[
            "story-group" => "top",
            "tag" => $tag,
            "limit" => 8
        ];
      $pickedStories = $this->client->stories($params);

      return view("tag", $this->toView([
          "stories" => $pickedStories,
          "sectionName" => $tag
        ])
      );
    }

    public function search($searchKey){
      $params =[
            "q" => $searchKey,
            "limit" => 8
        ];
      $pickedStories = $this->client->search($params);

      return view("search", $this->toView([
          "stories" => $pickedStories,
          "sectionName" => $searchKey
        ])
      );
    }
}

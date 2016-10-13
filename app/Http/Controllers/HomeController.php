<?php

namespace App\Http\Controllers;

use Log;
use App\Http\Controllers\QuintypeController;

use Quintype\Api\Bulk;
use Quintype\Api\StoriesRequest;

class HomeController extends QuintypeController{

    public function index(){
      $this->client->addBulkRequest("top_stories", "top", ["fields" => $this->fields, "limit" => 8]);//Default stack.
      $this->client->buildStacksRequest($this->stacks, $this->fields);//Build all stack requests dynamically.
      $this->client->executeBulk();//Use the bulk request and make the API call.
      $top_stories = $this->client->getBulkResponse("top_stories");//Get just the default stack stories.
      $stacks = $this->client->buildStacks($this->stacks);//Get all stacks stories.

      return view("home", $this->toView([
          "stories" => $top_stories,
          "stacks" => $stacks
        ])
      );
    }

    public function story($category, $y, $m, $d, $slug) {
      $story = $this->client->storyBySlug(["slug"=> $slug]);//Get the story details.
      $relatedStories = $this->client->relatedStories($story["id"]);//Get all the stories related to this story.
      $comments = $this->client->storyComments($story["id"]);//Get all the comments for this story.

      return view("story", $this->toView([
          "story" => $story,
          "relatedStories" => $relatedStories,
          "comments" => $comments
        ])
      );
    }

    public function section($section) {
      $sections = $this->config["sections"];//Get all sections.
      $cur_section_index = array_search($section, array_column($sections, "slug"), true);//Get the index of the selected section.
      if($cur_section_index !== false){
        $cur_section =  $sections[$cur_section_index];//Get details of the selected section.
        $params = [
            "story-group" => "top",
            "section-id" => $cur_section["id"],
            "limit" => 8,
            "fields" => $this->fields
        ];
        $stories = $this->client->stories($params);

        return view("section", $this->toView([
            "stories" => $stories,
            "sectionName" => $cur_section["name"],
            "sectionId" => $cur_section["id"]
          ])
        );
      } else {
        return "404 Page";
      }
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

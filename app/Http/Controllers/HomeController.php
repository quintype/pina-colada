<?php

namespace App\Http\Controllers;

use Log;
use App\Http\Controllers\QuintypeController;

use Quintype\Api\Bulk;
use Quintype\Api\StoriesRequest;

class HomeController extends QuintypeController{

    public function index(){
      $this->client->addBulkRequest("top_stories", "top", ["fields" => config("quintype.fields"), "limit" => 8]);//Default stack.
      $this->client->buildStacksRequest($this->config["layout"]["stacks"], config("quintype.fields"));//Build all stack requests dynamically.
      $this->client->executeBulk();//Use the bulk request and make the API call.
      $top_stories = $this->client->getBulkResponse("top_stories");//Get just the default stack stories.
      $stacks = $this->client->buildStacks($this->config["layout"]["stacks"]);//Get all stacks stories.

      $page = ["type" => "home"];
      //Set SEO meta tags.
      $setSeo = $this->seo->home($page["type"]);
      $this->meta->set($setSeo->prepareTags());

      return view("home/index", $this->toView([
          "stories" => $top_stories,
          "stacks" => $stacks,
          "page" => $page,
          "meta" => $this->meta
        ])
      );
    }

    public function story($category, $y, $m, $d, $slug) {
      $story = $this->client->storyBySlug(["slug"=> $slug]);//Get the story details.
      $relatedStories = $this->client->relatedStories($story["id"]);//Get all the stories related to this story.
      $comments = $this->client->storyComments($story["id"]);//Get all the comments for this story.

      $page = ["type" => "story"];
      //Set SEO meta tags.
      $setSeo = $this->seo->story($page["type"], $story);
      $this->meta->set($setSeo->prepareTags());

      return view("story", $this->toView([
          "story" => $story,
          "relatedStories" => $relatedStories,
          "comments" => $comments,
          "page" => $page,
          "meta" => $this->meta
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
            "fields" => config("quintype.fields")
        ];
        $stories = $this->client->stories($params);

        $page = ["type" => "section"];
        //Set SEO meta tags.
        $setSeo = $this->seo->section($page["type"], $cur_section["name"], $cur_section["id"]);
        $this->meta->set($setSeo->prepareTags());

        return view("section", $this->toView([
            "stories" => $stories,
            "sectionName" => $cur_section["name"],
            "sectionId" => $cur_section["id"],
            "page" => $page,
            "meta" => $this->meta
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
            "fields" => config("quintype.fields")
        ];
      $authorStories = $this->client->search($params);

      return view("author/index", $this->toView([
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

      $page = ["type" => "tag"];
      //Set SEO meta tags.
      $setSeo = $this->seo->tag($tag);
      $this->meta->set($setSeo->prepareTags());

      return view("tag", $this->toView([
          "stories" => $pickedStories,
          "sectionName" => $tag,
          "page" => $page,
          "meta" => $this->meta
        ])
      );
    }

    public function search($searchKey){
      $params =[
            "q" => $searchKey,
            "limit" => 8
        ];
      $pickedStories = $this->client->search($params);

      $page = ["type" => "search"];
      //Set SEO meta tags.
      $setSeo = $this->seo->search($searchKey);
      $this->meta->set($setSeo->prepareTags());

      return view("search/index", $this->toView([
          "stories" => $pickedStories,
          "sectionName" => $searchKey,
          "page" => $page,
          "meta" => $this->meta
        ])
      );
    }
}

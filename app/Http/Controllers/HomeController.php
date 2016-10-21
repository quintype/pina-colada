<?php

namespace App\Http\Controllers;

use Log;
use App\Http\Controllers\QuintypeController;
use Illuminate\Http\Request;
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

      return response(view("home/index", $this->toView([
          "stories" => $top_stories,
          "stacks" => $stacks,
          "page" => $page,
          "meta" => $this->meta
        ])
      ))->withHeaders([
          "Cache-Control" => "public,max-age=0",
          "Surrogate-Control" => "public,max-age=30,stale-while-revalidate=120,stale-if-error=3600",
          "Surrogate-Key" => $this->getKeys(["top/home"], $this->client->getBulkResponse("top_stories")),
          "Vary" => "Accept"
      ]);
    }

    public function story($category, $y, $m, $d, $slug) {
      $story = $this->client->storyBySlug(["slug"=> $slug]);//Get the story details.
      $relatedStories = $this->client->relatedStories($story["id"]);//Get all the stories related to this story.
      $comments = $this->client->storyComments($story["id"]);//Get all the comments for this story.

      $this->client->addBulkRequest("top_stories", "top", ["fields" => config("quintype.fields"), "limit" => 8]);//Default stack.
      $this->client->executeBulk();//Use the bulk request and make the API call.

      $page = ["type" => "story"];
      //Set SEO meta tags.
      $setSeo = $this->seo->story($page["type"], $story);
      $this->meta->set($setSeo->prepareTags());

      return response(view("story/index", $this->toView([
          "story" => $story,
          "relatedStories" => $relatedStories,
          "comments" => $comments,
          "page" => $page,
          "meta" => $this->meta
        ])
      ))->withHeaders([
          "Cache-Control" => "public,max-age=0",
          "Surrogate-Control" => "public,max-age=30,stale-while-revalidate=120,stale-if-error=3600",
          "Surrogate-Key" => $this->getKeys(["related/stories"], array_merge([$story], $this->client->getBulkResponse("top_stories"))),
          "Vary" => "Accept"
      ]);
    }

    public function section($section, $subSection = ''){
      $sections = $this->config["sections"];//Get all sections.

      $cur_section_index = array_search($section, array_column($sections, "slug"), true);//Get the index of given section.
      if($cur_section_index !== false){//Given section found.
        $cur_section =  $sections[$cur_section_index];//Get details of given section.
      } else {//Given section not found.
        return "404 Page";
      }

      if($subSection == ''){//If there is no sub section.
        return $this->getSectionData($cur_section);
      } else {//If there is a sub section.
        $cur_sub_section_index = array_search($subSection, array_column($sections, "slug"), true);//Get the index of given sub section.
        if($cur_sub_section_index !== false){//Given sub section found.
          $cur_sub_section =  $sections[$cur_sub_section_index];//Get details of given sub section.
          if($cur_section['id'] == $cur_sub_section['parent-id']){//Make sure the sub section belongs the given parent section.
            return $this->getSectionData($cur_sub_section);
          } else {
            return "404 Page";
          }
        } else {//Given sub section not found.
          return "404 Page";
        }
      }

    }

    public function getSectionData($section) {
      $params = [
          "story-group" => "top",
          "section-id" => $section["id"],
          "limit" => 8,
          "fields" => config("quintype.fields")
      ];
      $stories = $this->client->stories($params);

      $page = ["type" => "section"];
      //Set SEO meta tags.
      $setSeo = $this->seo->section($page["type"], $section["name"], $section["id"]);
      $this->meta->set($setSeo->prepareTags());

      return view("section/index", $this->toView([
          "stories" => $stories,
          "sectionName" => $section["name"],
          "sectionId" => $section["id"],
          "page" => $page,
          "meta" => $this->meta
        ])
      );
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

    public function tag(Request $request){
      $tag = $request->tag;
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

      return view("tag/index", $this->toView([
          "stories" => $pickedStories,
          "sectionName" => $tag,
          "page" => $page,
          "meta" => $this->meta
        ])
      );
    }

    public function search(Request $request){
      $searchKey = $request->q;
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

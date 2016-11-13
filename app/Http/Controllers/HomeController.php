<?php

namespace App\Http\Controllers;

use Log;
use App\Http\Controllers\QuintypeController;
use Illuminate\Http\Request;
use Quintype\Api\Bulk;
use Quintype\Api\StoriesRequest;

class HomeController extends QuintypeController{

    public function __construct(){
      parent::__construct();
      $this->fields = "id,headline,slug,url,hero-image-s3-key,hero-image-metadata,first-published-at,last-published-at,alternative,published-at,author-name,author-id,sections,story-template,summary,metadata,hero-image-attribution,cards,subheadline,authors";
      $this->loadMoreFields = "sections,hero-image-s3-key,headline,author-name,summary,first-published-at,slug";
    }

    public function index(){
      $this->client->addBulkRequest("top_stories", "top", ["fields" => $this->fields, "limit" => 8]);//Default stack.
      $this->client->buildStacksRequest($this->config["layout"]["stacks"], $this->fields);//Build all stack requests dynamically.
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
          "Surrogate-Key" => $this->client->getKeys(["top/home"], $this->client->getBulkResponse("top_stories"), $this->config["publisher-id"]),
          "Vary" => "Accept"
      ]);
    }

    public function story($category, $y, $m, $d, $slug) {
      $story = $this->client->storyBySlug(["slug"=> $slug]);//Get the story details.
      $relatedStories = $this->client->relatedStories($story["id"]);//Get all the stories related to this story.
      $comments = $this->client->storyComments($story["id"]);//Get all the comments for this story.

      $this->client->addBulkRequest("top_stories", "top", ["fields" => $this->fields, "limit" => 8]);//Default stack.
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
          "Surrogate-Key" => $this->client->getKeys(["related/stories"], array_merge([$story], $relatedStories), $this->config["publisher-id"]),
          "Vary" => "Accept"
      ]);
    }

    public function section($sectionName){
      $allSections = $this->config["sections"];
      $section = $this->client->getSectionDetails($sectionName, $allSections);
      if($section){
        $storyCount = 8;
        $params = [
            "story-group" => "top",
            "section-id" => $section["id"],
            "limit" => $storyCount + 1,
            "fields" => $this->fields
        ];
        $stories = $this->client->stories($params);

        $page = ["type" => "section"];
        //Set SEO meta tags.
        $setSeo = $this->seo->section($page["type"], $section["name"], $section["id"]);
        $this->meta->set($setSeo->prepareTags());

        return view("section/index", $this->toView([
            "stories" => array_slice($stories, 0, $storyCount),
            "sectionName" => $section["name"],
            "sectionId" => $section["id"],
            "page" => $page,
            "meta" => $this->meta,
            "loadMoreFields" => $this->loadMoreFields,
            "storyCount" => $storyCount,
            "showLoadMore" => sizeof($stories) > $storyCount
          ])
        );
      } else {
        return view("errors/404", $this->toView([]));
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

      return view("author/index", $this->toView([
          "authorDetails" => $authorDetails,
          "authorStories" => $authorStories
        ])
      );
    }

    public function tag(Request $request){
      $tag = $request->tag;
      $storyCount = 8;
      $params =[
            "story-group" => "top",
            "tag" => $tag,
            "limit" => $storyCount + 1
        ];
      $pickedStories = $this->client->stories($params);

      $page = ["type" => "tag"];
      //Set SEO meta tags.
      $setSeo = $this->seo->tag($tag);
      $this->meta->set($setSeo->prepareTags());

      if(sizeof($pickedStories) < 1){
        return view("errors/no_results", $this->toView([]));
      } else {
        return view("tag/index", $this->toView([
            "stories" => array_slice($pickedStories, 0, $storyCount),
            "tag" => $tag,
            "page" => $page,
            "meta" => $this->meta,
            "loadMoreFields" => $this->loadMoreFields,
            "storyCount" => $storyCount,
            "showLoadMore" => sizeof($pickedStories) > $storyCount
          ])
        );
      }
    }

    public function search(Request $request){
      $searchKey = $request->q;
      $storyCount = 8;
      $params =[
            "q" => $searchKey,
            "limit" => $storyCount + 1
        ];
      $pickedStories = $this->client->search($params);

      $page = ["type" => "search"];
      //Set SEO meta tags.
      $setSeo = $this->seo->search($searchKey);
      $this->meta->set($setSeo->prepareTags());
      if(sizeof($pickedStories) < 1){
        return view("errors/no_results", $this->toView([]));
      } else {
        return view("search/index", $this->toView([
            "stories" => array_slice($pickedStories, 0, $storyCount),
            "searchKey" => $searchKey,
            "page" => $page,
            "meta" => $this->meta,
            "loadMoreFields" => $this->loadMoreFields,
            "storyCount" => $storyCount,
            "showLoadMore" => sizeof($pickedStories) > $storyCount
          ])
        );
      }
    }
}

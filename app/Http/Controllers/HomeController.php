<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends QuintypeController
{
    public function __construct()
    {
        parent::__construct();
        $this->fields = 'id,headline,slug,url,hero-image-s3-key,hero-image-metadata,first-published-at,last-published-at,alternative,published-at,author-name,author-id,sections,story-template,summary,metadata,hero-image-attribution,cards,subheadline,authors';
        $this->loadMoreFields = 'sections,hero-image-s3-key,headline,author-name,summary,first-published-at,slug';
    }

    public function index()
    {
        $page = ['type' => 'home'];
        $this->client->addBulkRequest('top_stories', 'top', ['fields' => $this->fields, 'limit' => 8]);
        $this->client->buildStacksRequest($this->allStacks, $this->fields);
        $this->client->executeBulk();
        $top_stories = $this->client->getBulkResponse('top_stories');
        $stacks = $this->client->buildStacks($this->allStacks);

        //Set SEO meta tags.
        $setSeo = $this->seo->home($page['type']);
        $this->meta->set($setSeo->prepareTags());

        return response(view('home/index', $this->toView([
          'stories' => $top_stories,
          'stacks' => $stacks,
          'page' => $page,
          'meta' => $this->meta,
        ])))->withHeaders($this->caching->buildCacheHeaders(array_merge($this->defaultCacheParams, ['locationId' => 'home', 'storyGroup' => 'top', 'storiesToCache' => $top_stories])));
    }

    public function story($category, $y, $m, $d, $slug)
    {
        $page = ['type' => 'story'];
        $story = $this->client->storyBySlug(['slug' => $slug]);
        $relatedStories = $this->client->relatedStories($story['id']);
        $storiesToCache = array_merge([$story], $relatedStories);
        $getRatingValues = $this->getAverageRating($story);
        //Set SEO meta tags.
        $setSeo = $this->seo->story($page['type'], $story);
        $this->meta->set($setSeo->prepareTags());

        return response(view('story/index', $this->toView([
        'story' => $story,
        'relatedStories' => $relatedStories,
        'getRatingValues' => $getRatingValues,
        'page' => $page,
        'meta' => $this->meta,
      ])))->withHeaders($this->caching->buildCacheHeaders(array_merge($this->defaultCacheParams, ['storiesToCache' => $storiesToCache])));
    }

    public function section($sectionSlug, $subSectionSlug = '')
    {
        $page = ['type' => 'section'];
        $allSections = $this->config['sections'];
        $section = $this->client->getSectionDetails($sectionSlug, $allSections);

        if (sizeof($section) > 0) {
            $sectionId = $section['id'];
            $sectionName = $section['slug'];
        } else {
            return $this->pageNotFound();
        }

        if ($subSectionSlug !== '') {
            $subSection = $this->client->getSectionDetails($subSectionSlug, $allSections);
            if (sizeof($subSection) > 0) {
                if ($subSection['parent-id'] == $section['id']) {
                    $sectionId = $subSection['id'];
                    $sectionName = $subSection['slug'];
                } else {
                    return $this->pageNotFound();
                }
            } else {
                return $this->pageNotFound();
            }
        }

        $storyCount = 8;
        $params = [
            'story-group' => 'top',
            'section-id' => $sectionId,
            'limit' => $storyCount + 1,
            'fields' => $this->fields,
        ];
        $stories = array_slice($this->client->stories($params), 0, $storyCount);

        //Set SEO meta tags.
        $setSeo = $this->seo->section($page['type'], $sectionName, $sectionId);
        $this->meta->set($setSeo->prepareTags());

        if (sizeof($stories) > 0) {
            return response(view('section/index', $this->toView([
            'stories' => $stories,
            'sectionName' => $sectionName,
            'sectionId' => $sectionId,
            'page' => $page,
            'meta' => $this->meta,
            'loadMoreFields' => $this->loadMoreFields,
            'storyCount' => $storyCount,
            'showLoadMore' => (sizeof($stories) + 1) > $storyCount,
          ])))->withHeaders($this->caching->buildCacheHeaders(array_merge($this->defaultCacheParams, ['locationId' => $sectionId, 'storyGroup' => $params['story-group'], 'storiesToCache' => $stories])));
        } else {
          return view('errors/no_results', $this->toView([]));
        }
    }

    public function author($authorId)
    {
        $authorDetails = $this->client->getAuthor($authorId);
        $params = [
            'author-id' => $authorId,
            'sort' => 'latest-published',
            'limit' => 4,
            'fields' => $this->fields,
        ];
        $authorStories = $this->client->search($params);

        return view('author/index', $this->toView([
          'authorDetails' => $authorDetails,
          'authorStories' => $authorStories,
        ])
      );
    }

    public function tag(Request $request)
    {
        $page = ['type' => 'tag'];
        $tag = $request->tag;
        $storyCount = 8;
        $params = [
            'story-group' => 'top',
            'tag' => $tag,
            'limit' => $storyCount + 1,
        ];
        $pickedStories = array_slice($this->client->stories($params), 0, $storyCount);

      //Set SEO meta tags.
      $setSeo = $this->seo->tag($tag);
        $this->meta->set($setSeo->prepareTags());

        if (sizeof($pickedStories) < 1) {
            return view('errors/no_results', $this->toView([]));
        } else {
            return view('tag/index', $this->toView([
            'stories' => $pickedStories,
            'tag' => $tag,
            'page' => $page,
            'meta' => $this->meta,
            'loadMoreFields' => $this->loadMoreFields,
            'storyCount' => $storyCount,
            'showLoadMore' => (sizeof($pickedStories) + 1) > $storyCount,
          ])
        );
        }
    }

    public function search(Request $request)
    {
        $page = ['type' => 'search'];
        $searchKey = $request->q;
        $storyCount = 8;
        $params = [
            'q' => $searchKey,
            'limit' => $storyCount + 1,
        ];
        $pickedStories = array_slice($this->client->search($params), 0, $storyCount);

      //Set SEO meta tags.
      $setSeo = $this->seo->search($searchKey);
        $this->meta->set($setSeo->prepareTags());
        if (sizeof($pickedStories) < 1) {
            return view('errors/no_results', $this->toView([]));
        } else {
            return view('search/index', $this->toView([
            'stories' => $pickedStories,
            'searchKey' => $searchKey,
            'page' => $page,
            'meta' => $this->meta,
            'loadMoreFields' => $this->loadMoreFields,
            'storyCount' => $storyCount,
            'showLoadMore' => (sizeof($pickedStories) + 1) > $storyCount,
          ])
        );
        }
    }
}

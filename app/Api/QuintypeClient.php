<?php

namespace App\Api;

use GuzzleHttp\Client;

class QuintypeClient
{
	public function __construct()
	{
		$this->client = new Client([
		    'base_uri' => 'http://swarajyamag.com',
		]);
	}

	private function getResponse($query = null)
	{
		$request  = $this->client->get($query);
		$response = $request->getBody()->getContents();
	
		return $response;
	}

	public function menu()
    {
    	$query 	  = '/api/config';
		$response = $this->getResponse($query);
        
        return $response;
    }

    public function articles($categoryId = null, $limit = null, $offset = null)
    {
    	$query = '/api/stories';

        if ($categoryId == 'all-issues') {
            $query = '/api/story-collection';
        }

        if ($categoryId && $categoryId != 'all-issues') {
    		$query .= '?section-id='.$categoryId;
    	}

    	if ($limit) {
    		$query .= '&limit='.$limit;
    	}

        if ($offset) {
            $query .= '&offset='.$offset;
        }
        
        $response = json_decode($this->getResponse($query), true);

        return $response;
    }

    public function article($slug)
    {
        $query = '/api/stories-by-slug?slug='.$slug;

        $response = $this->getResponse($query);
                
        return $response;
    }
    public function article_by_section($section_slug,$limit)
    {
        $query = '/api/stories';

        if ($section_slug) {
            $query .= '?section-id='.$section_slug;
        }

        $response = $this->getResponse($query);

        return $response;
    }

    public function articles_by_tag($topic)
    {
        $query = '/api/stories?tag='.urlencode($topic);

        $response = $this->getResponse($query);
        
        return $response;
    }
    

    public function search_articles($search_for)
    {
        $query = '/api/v1/search?q='.$search_for;

        $response = $this->getResponse($query);

        return $response = $response['results']['stories'];
    }

    public function issues_by_years($issues = null)
    {
        $result = [];
        foreach ($issues as $issue) {
            $year = date('Y', $issue['updated-at']/1000);
            $result[$year][] = $issue;
        }

        return $result;
    }

    public function getAllArticles()
    {
        $data = json_encode(['requests' =>
                            [
                                'featured' => [
                                                '_type' => 'stories',
                                                'section' => 'Featured',
                                                'limit' => 13
                                              ],
                                'politics' => [
                                                '_type' => 'stories',
                                                'section' => 'Politics',
                                                'limit' => 4
                                              ],
                                'economy'  => [
                                                '_type' => 'stories',
                                                'section' => 'Economy',
                                                'limit' => 4
                                              ],
                                'business' => [
                                                '_type' => 'stories',
                                                'section' => 'Business',
                                                'limit' => 3
                                              ],
                                'world'  => [
                                                '_type' => 'stories',
                                                'section' => 'World',
                                                'limit' => 3
                                              ],
                                'ideas' => [
                                                '_type' => 'stories',
                                                'section' => 'Ideas',
                                                'limit' => 3
                                              ],
                                'culture'  => [
                                                '_type' => 'stories',
                                                'section' => 'Culture',
                                                'limit' => 3
                                              ]
                            ]
                        ]);

        $service_url = 'http:.//swarajyamag.com/api/bulk';
        $ch = curl_init($service_url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_URL, $service_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ch_result = curl_exec($ch);
        curl_close($ch);

        $articles = json_decode($ch_result, true);
        return $articles['results'];
    }

    public function getSingleIssue($id = null)
    {
        $query = '/api/story-collection/?id='.$id.'&fields=id,headline,slug,hero-image-s3-key,author-id,author-name';
        $issue = $this->getResponse($query);
        
        return $issue;
    }

    public function getAuthor($id = null)
    {
        $query = '/api/author/'.$id;
        $response = $this->getResponse($query);;

        return $response;
    }
}
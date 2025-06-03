<?php

namespace App\Util;

use App\Models\ScraperPreference;
use Illuminate\Support\Facades\Redis;
use Log;

class GoogleSearch{

    const BASE_URL = "https://www.googleapis.com/customsearch/v1";
    const DOC_URL = "https://developers.google.com/custom-search/v1/reference/rest/v1/cse/list";
    const MAX_RESULTS = 10; // Set by google's API - going over will cause errors

    static function find($params = [], $items_per_page = 10, $page = 1){

        # prevent min/max items per page
        if($items_per_page < 1 || $items_per_page > self::MAX_RESULTS) {
            $items_per_page = self::MAX_RESULTS;
        }

        $params = [
            'q' => $params['q'],
            'key' => config('google.api_key'),
            'cx' => config('google.search_engine_id'),
            'start' => (($page - 1) * $items_per_page) + 1,
            'num' => $items_per_page,
        ];

        $response = Web::get(self::BASE_URL, $params)['data'];
        return $response;
    }

    static function findScraperPreferenceLinks(ScraperPreference $scraperPreference, $items_per_page = 10, $page = 1){
        $cacheKey = 'search_params:' . md5($scraperPreference->description);
        $cachedParams = Redis::get($cacheKey);
        
        if ($cachedParams) {
            $params = json_decode($cachedParams, true);
        } else {
            $params = self::generateAISearchParams($scraperPreference);
            Redis::setex($cacheKey, 3600 * 0.5, json_encode($params)); // cache for 30 minutes
        }

        $google_response = self::find($params, $items_per_page, $page);
        $links = self::extractLinksFromResponse($google_response);
        
        return $links;
    }

    static function generateAISearchParams(ScraperPreference $searchPreference){
        $param_array = null;
        $max_attempts = 3;
        $attempts = 0;

        while(empty($param_array) && $attempts < $max_attempts){
            try {
                $ai = new AI();
                $message = "
                    Your goal is create the ideal search query for users's search preference
                    Your response MUST, without exception, be a json object response where each key-value pair represents a Google Search API parameter and its value
                    the 'q' param should be the main focus of the search query and please ignore 'key' as this will be overriden
                    Here is summary of the Google Search API parameters: ".self::generateAIGoogleDocs()."
                    Your parameter response should try and maximize results and not be too restrictive on search terms or filters
                    Here is the user's search preference: '{$searchPreference->description}'
                    If your response returns anything but a decodable json object it will be rejected
                ";
                $ai_response = $ai->chat($message, 'system')->response();
                
                $decoded = json_decode($ai_response, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && !empty($decoded) && array_keys($decoded) !== range(0, count($decoded) - 1)) {
                    $param_array = $decoded;
                    Log::info("Valid AI response received on attempt: " . ($attempts + 1));
                    break;
                } else {
                    throw new \Exception("Invalid JSON response: " . $ai_response);
                }
            } catch (\Exception $e) {
                Log::warning("Error processing AI response. Attempt: " . ($attempts + 1) . ". Error: " . $e->getMessage());
                $param_array = null;
            }

            $attempts++;
        }

        if (empty($param_array)) {
            Log::error("Failed to get a valid response from AI after $max_attempts attempts.");
            throw new \RuntimeException("Unable to generate valid search parameters after $max_attempts attempts.");
        }

        return $param_array;
    }

    static function generateAIGoogleDocs(){

        $page = Redis::get(self::DOC_URL);
        if (!$page) {
            $res = Web::get(self::DOC_URL)['data'];
            $page = $res;
            Redis::setex(self::DOC_URL, config('cache.redis_expire_time'), $res);
        }
        $google_api_params = '';
        if($page){

            $google_api_params = Redis::get('google.api_params');

            if(!$google_api_params){
                $ai = new AI();
                $response = $ai->chat(
                    `Consider the Google Search API page:`.$page.
                    `Your output will be used to generate an ideal search query for and LLM as a basic guide.
                    Your response must be a refined summerization of the page including key details that would be relevant when generating an api request`)->response();
                Redis::setex('google.api_params', config('cache.redis_expire_time'), $response);
                $google_api_params = $response;
            }
        }

        return $google_api_params;
    }

    static function extractLinksFromResponse($response) {
        $links = [];

        if (is_string($response)) {
            $response = json_decode($response, true);
        }
        if (is_array($response) && isset($response['items']) && is_array($response['items'])) {
            foreach ($response['items'] as $item) {
                if (isset($item['link'])) {
                    $links[] = $item['link'];
                }
            }
        }

        return $links;
    }
}
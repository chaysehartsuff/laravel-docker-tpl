<?php

namespace App\Util;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Spatie\Browsershot\Browsershot;
use Exception;
use Illuminate\Support\Facades\Redis;

class Web
{
    private static $proxyConfig = null;
    private static $cacheExpiration = 3600 * 48; // 48 hrs

    /**
     * Configure a proxy for the next request.
     *
     * @param string $host
     * @param int $port
     * @param string|null $username
     * @param string|null $password
     * @return self
     */
    public static function proxy(string $host, int $port, ?string $username = null, ?string $password = null): self
    {
        self::$proxyConfig = [
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'password' => $password,
        ];

        return new static();
    }

    /**
     * Make a GET request to the specified URL.
     *
     * @param string $url
     * @param array $params
     * @param array $headers
     * @return array
     */
    public static function get(string $url, array $params = [], array $headers = []): array
    {
        $baseUrl = self::getBaseUrl($url);
        $mergedParams = self::extractAndMergeParams($url, $params);
        $cacheKey = $baseUrl . '?' . http_build_query($mergedParams);

        // Check if the response is cached
        $cachedResponse = Redis::get($cacheKey);
        if ($cachedResponse) {
            $parsedResponse = json_decode($cachedResponse, true);
            if(!$parsedResponse['success'])
                throw new Exception('Request failed: '. $parsedResponse['error']);
            return $parsedResponse;
        }

        try {
            $http = self::configureHttp($headers);
            $response = $http->get($baseUrl, $mergedParams);

            $result = self::handleResponse($response);

            // Cache the result
            Redis::setex($cacheKey, self::$cacheExpiration, json_encode($result));

            return $result;
        } catch (RequestException $e) {
            return self::handleException($e);
        } finally {
            self::clearProxyConfig();
        }
    }

    /**
     * Make a GET request to the specified URL and render JavaScript.
     *
     * @param string $url
     * @param array $params
     * @param array $headers
     * @param int $timeout
     * @return array
     */
    public static function getRendered(string $url, array $params = [], array $headers = [], int $timeout = 30000): array
    {
        $baseUrl = self::getBaseUrl($url);
        $mergedParams = self::extractAndMergeParams($url, $params);
        $cacheKey = 'rendered:' . $baseUrl . '?' . http_build_query($mergedParams);

        // Check if the response is cached
        $cachedResponse = Redis::get($cacheKey);
        if ($cachedResponse) {
            return json_decode($cachedResponse, true);
        }

        try {
            $fullUrl = $baseUrl . (empty($mergedParams) ? '' : '?' . http_build_query($mergedParams));
            
            $html = Browsershot::url($fullUrl)
                ->setExtraHttpHeaders($headers)
                ->waitUntilNetworkIdle()
                ->timeout($timeout)
                ->bodyHtml();

            $result = [
                'success' => true,
                'status_code' => 200,
                'data' => $html,
            ];

            // Cache the result
            Redis::setex($cacheKey, self::$cacheExpiration, json_encode($result));

            return $result;
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'status_code' => 500,
                'error' => $e->getMessage(),
            ];

            // Cache the error result for a shorter period
            Redis::setex($cacheKey, 300, json_encode($result)); // Cache errors for 5 minutes

            return $result;
        }
    }

    /**
     * Make a POST request to the specified URL.
     *
     * @param string $url
     * @param array $data
     * @param array $headers
     * @return array
     */
    public static function post(string $url, array $data = [], array $headers = []): array
    {
        try {
            $http = self::configureHttp($headers);
            $response = $http->post($url, $data);

            return self::handleResponse($response);
        } catch (RequestException $e) {
            return self::handleException($e);
        } finally {
            self::clearProxyConfig();
        }
    }

    /**
     * Configure the HTTP client with headers and proxy if set.
     *
     * @param array $headers
     * @return \Illuminate\Http\Client\PendingRequest
     */
    private static function configureHttp(array $headers): \Illuminate\Http\Client\PendingRequest
    {
        $http = Http::withHeaders($headers);

        if (self::$proxyConfig) {
            $proxyUrl = self::$proxyConfig['host'] . ':' . self::$proxyConfig['port'];
            if (self::$proxyConfig['username'] && self::$proxyConfig['password']) {
                $proxyUrl = self::$proxyConfig['username'] . ':' . self::$proxyConfig['password'] . '@' . $proxyUrl;
            }
            $http->withOptions(['proxy' => $proxyUrl]);
        }

        return $http;
    }

    /**
     * Clear the proxy configuration after each request.
     */
    private static function clearProxyConfig(): void
    {
        self::$proxyConfig = null;
    }

    /**
     * Handle the HTTP response.
     *
     * @param \Illuminate\Http\Client\Response $response
     * @return array
     */
    private static function handleResponse($response): array
    {
        if ($response->successful()) {
            return [
                'success' => true,
                'status_code' => $response->status(),
                'data' => $response->json() ?: $response->body(),
            ];
        } else {
            return [
                'success' => false,
                'status_code' => $response->status(),
                'error' => $response->body(),
            ];
        }
    }

    /**
     * Handle exceptions from the HTTP client.
     *
     * @param RequestException $e
     * @return array
     */
    private static function handleException(RequestException $e): array
    {
        return [
            'success' => false,
            'status_code' => $e->getCode(),
            'error' => $e->getMessage(),
        ];
    }

    /**
     * Extract parameters from a URL and merge them with provided params.
     *
     * @param string $url
     * @param array $params
     * @return array
     */
    private static function extractAndMergeParams(string $url, array $params = []): array
    {
        $parsedUrl = parse_url($url);
        $urlParams = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $urlParams);
        }
        return array_merge($urlParams, $params); // $params take precedence
    }

    /**
     * Get the base URL without query parameters.
     *
     * @param string $url
     * @return string
     */
    private static function getBaseUrl(string $url): string
    {
        $parsedUrl = parse_url($url);
        $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
        if (isset($parsedUrl['port'])) {
            $baseUrl .= ':' . $parsedUrl['port'];
        }
        if (isset($parsedUrl['path'])) {
            $baseUrl .= $parsedUrl['path'];
        }
        return $baseUrl;
    }
}
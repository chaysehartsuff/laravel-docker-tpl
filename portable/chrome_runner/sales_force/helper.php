<?php

function typeInput($page, $selector, $value){
    $input = $page->dom()->querySelector($selector);
    $input->click();
    $input->sendKeys($value);
}

function waitForUrlEnd($page, $url_end){
    $start = microtime(true);
    $timeout = 10;

    while (true) {
        $url = $page->evaluate('window.location.href')->getReturnValue();

        if (str_ends_with($url, $url_end)) {
            break;
        }

        if ((microtime(true) - $start) > $timeout) {
            throw new \RuntimeException("Timeout waiting for URL to end in 'home'");
        }

        usleep(500_000);
    }

}

function waitForDomSelector($page, $selector, $timeout = 10) {
    $start = microtime(true);
    while (true) {
        $exists = $page->evaluate("document.querySelector('$selector') !== null")->getReturnValue();

        if ($exists) {
            break;
        }

        if ((microtime(true) - $start) > $timeout) {
            throw new \RuntimeException("Timeout waiting for selector '$selector'");
        }

        usleep(500_000);
    }
}

function trackNetworkRequests($page, array &$collector): void
{
    $session = $page->getSession();

    $session->sendMessageSync(new \HeadlessChromium\Communication\Message('Network.enable', []));

    $session->on('method:Network.requestWillBeSent', function ($params) use (&$collector) {
        $collector[] = (object)[
            'url' => $params['request']['url'] ?? null,
            'method' => $params['request']['method'] ?? null,
            'headers' => $params['request']['headers'] ?? [],
            'postData' => $params['request']['postData'] ?? null,
            'requestId' => $params['requestId'] ?? null,
            'timestamp' => $params['timestamp'] ?? null,
            'documentURL' => $params['documentURL'] ?? null,
        ];
    });
}


function findRequestByUrl(array $requests, string $match, bool $exact = false)
{
    foreach ($requests as $req) {
        if ($exact && $req->url === $match) {
            return $req;
        }

        if (!$exact && strpos($req->url, $match) !== false) {
            return $req;
        }
    }

    return null;
}



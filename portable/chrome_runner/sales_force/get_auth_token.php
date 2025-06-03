#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/helper.php';

use HeadlessChromium\BrowserFactory;

# config
const SF_LOGIN_URL="https://fwfllc.my.salesforce.com/";
const SF_CONTACTS_URL = "https://fwfllc.lightning.force.com/lightning/o/Contact/list?filterName=AllContacts";
const SF_AUTH_TOKEN_URL = "https://fwfllc.lightning.force.com/aura?preloadActions";

# parameters
$username = $argv[1];
$password = $argv[2];

# throw error if username or password not provided
if (!$username ||!$password) {
    echo "Usage: php login.php <username> <password>\n";
    exit(1);
}


$browserFactory = new BrowserFactory('/usr/bin/chromium');
$browser = $browserFactory->createBrowser([
    'headless' => true,
    'noSandbox' => true,
]);

$networkRequests = [];

$page = $browser->createPage();
trackNetworkRequests($page, $networkRequests);
$page->navigate(SF_LOGIN_URL)->waitForNavigation();

typeInput($page, '#username', $username);
typeInput($page, '#password', $password);
$page->dom()->querySelector('#Login')->click();
waitForUrlEnd($page, 'home');

$page->navigate(SF_CONTACTS_URL)->waitForNavigation();
waitForDomSelector($page, '[data-refid="recordId"]', 15);

sleep(5);

$auth_token_request = findRequestByUrl($networkRequests, SF_AUTH_TOKEN_URL);

echo extractAuraTokenFromRequest($auth_token_request);

$browser->close();


function extractAuraTokenFromRequest($request)
{
    $parsed = [];
    if (empty($request->postData)) {
        return null;
    }
    parse_str($request->postData, $parsed);

    return $parsed['aura_token'] ?? null;
}



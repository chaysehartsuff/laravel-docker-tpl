<?php

namespace App\Util;

class SalesForceAPI
{
    /**
     * Refresh the authentication token for Salesforce API.
     *
     * @param string $username The Salesforce username
     * @param string $password The Salesforce password
     * @return boolean sets status of operation and stores token automagically
     */
    public static function refreshAuthToken(string $username, string $password): ?string
    {
        $token = shell_exec("php /portable/chrome_runner/sales_force/get_auth_token.php $username $password");
        return $token;
    }
}
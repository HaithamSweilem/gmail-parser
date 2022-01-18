<?php

declare (strict_types = 1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../lib/client.php';

// this job is to be run once every hour

try {
    refreshTokens();
} catch (Exception $e) {
    die($e->getMessage());
}

/**
 * @throws \Google\Exception
 * @throws Exception
 */
function refreshTokens() {
    // refresh tokens
    // Get the API client and construct the service object.
    $client = getClient();
    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

            // Save the token to a file.
            $tokenPath = __DIR__ . '../lib/token.json';
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
        } else {
            // user is required here because using refresh token is not possible anymore
            throw new Exception("You need to re-initiate auth flow in order to obtain access/refresh tokens");
        }
    }
}

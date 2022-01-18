<?php
declare (strict_types = 1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../lib/client.php';

try {
    index();
} catch (\Google\Exception $e) {
    die($e->getMessage());
}

/**
 * @throws \Google\Exception
 */
function index() {

    $client = getClient();

    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();

            header('Location: ' . $authUrl);
            return;
        }
    }

    header('Location: /messages.php');
}

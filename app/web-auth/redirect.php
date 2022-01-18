<?php

declare (strict_types = 1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../lib/client.php';


try {
    run();
} catch (\Google\Exception $e) {
    die($e->getMessage());
} catch (Exception $e) {
    die($e->getMessage());
}

/**
 * @throws \Google\Exception
 * @throws Exception
 */
function run()
{
    $client = getClient();

    // http://localhost:8080/redirect?code=4/0AX4XfWgitqjL7H5ml53dMKOLYclEkWwKunYOG1dQwOD7IrSuWE3i-Gf21L3bt1DBTBPE7Q&scope=https://www.googleapis.com/auth/gmail.readonly
    // REQUEST_URI = "/redirect?code=4/0AX4XfWgitqjL7H5ml53dMKOLYclEkWwKunYOG1dQwOD7IrSuWE3i-Gf21L3bt1DBTBPE7Q&scope=https://www.googleapis.com/auth/gmail.readonly"
    $params = $_GET;
    $authCode = $params['code'];
    //        $scope = $params['scope'];

    // Exchange authorization code for an access token.
    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

    printf("Access token: " . print_r($accessToken, true));
    var_dump($accessToken);

    // Check to see if there was an error.
    if (array_key_exists('error', $accessToken)) {
        throw new Exception(join(', ', $accessToken));
    }

    $client->setAccessToken($accessToken);

    // Save the token to a file.
    $tokenPath = __DIR__ . '../lib/token.json';
    if (!file_exists(dirname($tokenPath))) {
        mkdir(dirname($tokenPath), 0700, true);
    }

    file_put_contents($tokenPath, json_encode($client->getAccessToken()));
}

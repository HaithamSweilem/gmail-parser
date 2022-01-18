<?php

declare (strict_types = 1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../lib/client.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

try {
    messages();
} catch (\Google\Exception|Exception $e) {
    die($e->getMessage());
}

/**
 * @throws \Google\Exception
 * @throws Exception
 */
function messages() {

    // Get the API client and construct the service object.
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

    // connect to queue
    $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    $channel = $connection->channel();
    $channel->queue_declare('FetchedMessages', false, false, false, false);
    // end - connect to queue

    $service = new Google_Service_Gmail($client);

    // Print the labels in the user's account.
    // put user email here:
    $user = 'haitham.sweilem@gmail.com';
    // query for order emails from amazon.co.uk and amazon.com
    $gmailQuery = 'from:(amazon.co.uk|Amazon.com) "your (amazon.co.uk|amazon.com) order of"';

    $filteredMessages = $service->users_messages->listUsersMessages($user, ['q' => $gmailQuery])->getMessages();

    foreach ($filteredMessages as $message) {
        $id = $message->id;
        $parts = $service->users_messages->get($user, $id)->getPayload()->getParts();
        foreach ($parts as $part) {
            $attachmentId = $part->getBody()->getAttachmentId();
            if (empty($attachmentId)) {
                $raw = $part->getBody()->getData();

                if (empty($raw)) {
                    continue;
                }

                $raw = base64_decode($raw) . "";
                if (preg_match("/Order Details/", $raw)) {
                    // filter out binary data that come at the end of the message
                    $filteredRaw = substr($raw, 0, stripos($raw, "Need to make changes to your order"));

                    $data = [
                        "user_id" => $user,
                        "raw_message" => $filteredRaw
                    ];
                    $msg = new AMQPMessage(json_encode($data));

                    echo ":::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: \n";
                    echo "Publishing a message for $user on queue: \n"
                        . print_r($data, true);
                    $channel->basic_publish($msg, '', 'FetchedMessages');
                }
            }
        }
    }

    // end job - close connections to broker
    $channel->close();
    $connection->close();
}

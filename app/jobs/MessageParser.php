<?php

declare (strict_types = 1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../lib/client.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;


try {
    parse();
} catch (ErrorException|Exception $e) {
    die($e->getMessage());
}

/**
 * @throws ErrorException
 * @throws Exception
 */
function parse() {

    $callback = function ($msg) {
        $message = json_decode($msg->body, true);
        echo "> Parsing message for user: " . $message["user_id"] . "\n";
//        echo "> Message Contents: " . print_r($message, true);

        preg_match_all('/Placed on.*[\n\s]*(.*)/', $message["raw_message"], $itemArray);
        echo "1. Item: " . $itemArray[1][0] . "\n";

        preg_match_all('/Order Total:\s+(\D+)([\d\-]+)/', $message["raw_message"], $priceArray);
        echo "2. Price: " . "(currency: " . $priceArray[1][0] . ") " . $priceArray[2][0] . "\n";

        preg_match_all('/Placed on(.*)/', $message["raw_message"], $placedOnArray);
        echo "3. Date of Purchase: " . $placedOnArray[1][0] . "\n";

        preg_match_all('/Sold by(.*)/', $message["raw_message"], $soldByArray);
        echo "4. Sold by: " . $soldByArray[1][0] . "\n";

        // TODO:
        // store this information into a table in database
        // SQL INSERT INTO purchase VALUES (...)
    };

    // connect to queue
    $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    $channel = $connection->channel();
    $channel->queue_declare('FetchedMessages', false, false, false, false);
    // end - connect to queue

    $channel->basic_consume('FetchedMessages', '', false, true, false, false, $callback);

    while ($channel->is_open()) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();
}

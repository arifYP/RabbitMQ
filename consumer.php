<?php

require __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection(
    getenv('RABBITMQ_HOST') ?: '127.0.0.1',
    getenv('RABBITMQ_PORT') ?: 5672,
    getenv('RABBITMQ_USER') ?: 'guest',
    getenv('RABBITMQ_PASSWORD') ?: 'guest'
);

$channel = $connection->channel();

$queue = getenv('RABBITMQ_QUEUE') ?: 'email_queue';

$channel->queue_declare($queue, false, true, false, false);

echo "message starting from queue...\n";

$callback = function ($msg) {
    $data = json_decode($msg->body, true);
    echo "sending email: {$data['email']}\n";
    echo "subject: {$data['subject']}\n";
    echo "message: {$data['message']}\n";
    echo "-----------------------------\n";
    sleep(1); // simulate processing time
};

$channel->basic_consume($queue, '', false, true, false, false, $callback);

// while (true) {
//     $channel->wait();
// }

// $channel->wait();

// while (count($channel->callbacks)) {
//     $channel->wait();
// }

while ($channel->callbacks) {
    try {
        $channel->wait(null, false, 10);
    } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $e) {
        echo "Timeout waiting for new messages...\n";
        break; 
    }
}


$channel->close();
$connection->close();

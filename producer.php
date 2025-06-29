<?php

require __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection(
    getenv('RABBITMQ_HOST') ?: '127.0.0.1',
    getenv('RABBITMQ_PORT') ?: 5672,
    getenv('RABBITMQ_USER') ?: 'guest',
    getenv('RABBITMQ_PASSWORD') ?: 'guest'
);

$channel = $connection->channel();

$queue = getenv('RABBITMQ_QUEUE') ?: 'email_queue';

$channel->queue_declare($queue, false, true, false, false);

// Example message data
$data = [
    'email' => 'user@example.com',
    'subject' => 'Welcome8!',
    // 'message' => 'Thank you for registering with us.'
    'message' => 'Thank you for registering with us3.'
];

$msg = new AMQPMessage(json_encode($data));
$channel->basic_publish($msg, '', $queue);

echo "Message sent to queue8.\n";

$channel->close();
$connection->close();

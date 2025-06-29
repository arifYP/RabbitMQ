<?php

use Illuminate\Support\Facades\Route;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/send-mail', function () {
    $data = [
        'email' => 'user@example.com',
        'subject' => 'welcome3',
        'message' => 'Your registration is completed'
    ];

    $connection = new AMQPStreamConnection(
        env('RABBITMQ_HOST'),
        env('RABBITMQ_PORT'),
        env('RABBITMQ_USER'),
        env('RABBITMQ_PASSWORD')
    );

    $channel = $connection->channel();

    $channel->queue_declare(env('RABBITMQ_QUEUE'), false, true, false, false);

    $msg = new AMQPMessage(json_encode($data), [
        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
    ]);

    $channel->basic_publish($msg, '', env('RABBITMQ_QUEUE'));

    $channel->close();
    $connection->close();

    return " Email data is send to RabbitMQ! ";
});

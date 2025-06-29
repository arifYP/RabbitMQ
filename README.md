# RabbitMQ

# 📧 RabbitMQ Email Notifier

This is a beginner-friendly project that shows how to send emails using a **message queue** system called **RabbitMQ**. Instead of sending the email directly, we put the email data in a **queue** and send it later using a **consumer** script. This makes your app faster and more scalable.

## 🛠 Technologies Used

- PHP 8+
- Laravel 10+
- RabbitMQ
- Composer
- php-amqplib/php-amqplib

- # 🔁 Basic Concepts (For Beginners)

### ✅ What is RabbitMQ?

RabbitMQ is a **message queue** system. It lets one part of your app (called a **producer**) send a message into a queue. Another part (called a **consumer**) reads the message and acts on it.

* The Producer sends messages (like "send this email") to a Queue.

* The Consumer listens to that queue and performs the task (like actually sending the email).

* This makes your app faster, more scalable, and well-organized.

* Great for background jobs: emails, SMS, image processing, etc.

> Imagine a restaurant:  
> - The **waiter (producer)** takes your order and puts it in the kitchen queue.  
> - The **chef (consumer)** takes the order from the queue and cooks it.  

This way, the waiter can take the next order without waiting for the food to be cooked.



## Why Use RabbitMQ?
✅ Faster apps: Your app doesn’t have to wait to send emails or process tasks.

🧠 Better structure: One service can send tasks, another service can do them.

📈 Scalability: Handles thousands or millions of messages efficiently.

🧵 Asynchronous processing: Let the system do things later, not immediately.

## When Should Use RabbitMQ?
✅ For things that take time or don’t need to happen instantly:

* Sending emails

* Processing images or videos

* Generating reports

* Sending SMS

* Background jobs

### 1️⃣ Clone the Project

```bash
git clone https://github.com/arifYP/RabbitMQ.git
cd RabbitMQ
```
## Setup Laravel .env File
```
RABBITMQ_HOST=127.0.0.1
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_QUEUE=email_queue
```
## Setup routes/web.php
```
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
```
## Setup RabbitMQ Server
### Use Docker (Recommended)
```
docker run -d --hostname rabbit --name rabbitmq \
  -p 5672:5672 -p 15672:15672 rabbitmq:3-management
```
* RabbitMQ UI: http://localhost:15672

* Default username/password: guest / guest

## Start Laravel API Server
```
php artisan serve
```
## Setup consumer.php
```
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
```
### Run the consumer.php script
```
php consumer.php
```
### Samle output
```
message starting from queue...
sending email: user@example.com
subject: Welcome!
message: Thank you for registering!
-----------------------------
```
## Setup producer.php
```
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
```
### Run the producer.php script
```
php producer.php
```
### Samle output
```
>php producer.php
Message sent to queue.
```
## Summary
When you hit the /send-mail API:
* Laravel controller receives the email info.
* It sends the data to the RabbitMQ queue.
* consumer.php reads the queue and sends the email using PHP's mail library.

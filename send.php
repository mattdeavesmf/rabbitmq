<?php
/**
* Pre-reqs:
*	brew install rabbitmq
*	brew info rabbitmq   (for instructions to start)
*
* Usage: 
*	php send.php "This is a task that will take 20 seconds to run...................."
*	php send.php "This is a task that will take 10 seconds to run.........."
*	php send.php "This is a task that will take 5 seconds to run....."
*/


require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

try {
	$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
	$channel = $connection->channel();
} catch (Exception $e) {
	echo $e->getMessage();
}

$channel->queue_declare('task_queue', false, true, false, false);

$data = implode(' ', array_slice($argv, 1));
if(empty($data)) {  
	$data = "Hello World.!";
}

$msg = new AMQPMessage($data, array('delivery_mode' => 2));
$channel->basic_publish($msg, '', 'task_queue');
$channel->close();
$connection->close();

echo " [x] Sent '" . $data . "'\n";

<?php
/**
* Pre-reqs:
*       brew install rabbitmq
*       brew info rabbitmq   (for instructions to start)
*
* Usage:
*       php receive.php
*
* Notes
*	Run multiples of this script to have mulitple workers picking up tasks from the queue
*
*
*/

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('task_queue', false, true, false, false);  //true declares the queue as permanent so RabbitMQ can crash/restart

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

/**
* This callback is used to do the work of the item in the queue.  The 'work' is represented by a 1 sec wait per . in the message
*/
$callback = function($msg) {
	echo date('Y-m-d H:i:s') . "'n";
	echo " [x] Received ", $msg->body, "\n";
	sleep(substr_count($msg->body, '.'));
  	echo " [x] Done", "\n";
	$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);	//make the task say when the job is finished and can be removed from the queue so worker can crash
};

$channel->basic_qos(null, 1, null);  //pre-fetch count set to one means RabbitMQ will only give 1 message at a time to a receiver
$channel->basic_consume('task_queue', '', false, false, false, false, $callback); //Setup what queue we should be listening to

//This kind of runner is so basic we are memory leak safe assuming php-amqplib is well written?
while(count($channel->callbacks)) {
    $channel->wait();
}

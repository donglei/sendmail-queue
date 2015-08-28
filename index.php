<?php
define('BASE_DIR', dirname(__FILE__));
require BASE_DIR . '/' . 'vendor/autoload.php';

$beanstalk_config = [
	'host' => '10.1.11.31',
	'port' => '11300',
	'tube' => 'mail'
];


use Pheanstalk\Pheanstalk;

$pheanstalk = new Pheanstalk($beanstalk_config['host'],$beanstalk_config['port']);

$logger = new Katzgrau\KLogger\Logger(__DIR__.'/logs', Psr\Log\LogLevel::DEBUG, array (
    'extension' => 'log', // changes the log file extension
));
$logger->info("start");
while ($job = $pheanstalk->reserveFromTube($beanstalk_config['tube'])) {
	$logger->info("Send Mail do With:: job id:" .  $job->getId() . ' data:' . $job->getData());
	$data = json_decode($job->getData(), true);
	if ($data['to'] == '' || $data['data'] == '') {
		$logger->info("Send Mail empty:: job id:" .  $job->getId());
		$pheanstalk->delete($job);
		continue;
	}
	
	try{
		sendMail($data);
	}
	catch(Exception $error)
	{
		$pheanstalk->release($job, 10, 10);
		$logger->error("Send Mail release:: job id:" .  $job->getId() . ' is error ' . $error->getMessage());		
		continue;
	}
	$logger->info("Send Mail release:: job id:" .  $job->getId());
	$pheanstalk->delete($job);
}

echo 'end', "\n";

function sendMail($data) 
{
	$send_mail = [
	'host' => 'smtp.putao.com',
	'port' => "587",
	'username' => 'no-reply@putao.com',
	'password' => 'putao@12345',
];


  // Create the Transport
   $transport = Swift_SmtpTransport::newInstance($send_mail['host'], $send_mail['port'], 'tls')
  			->setUsername($send_mail['username'])
  			->setPassword($send_mail['password']);
	// Create the Mailer using your created Transport
	$mailer = Swift_Mailer::newInstance($transport);

	// Create a message
	$message = Swift_Message::newInstance($data['subject'])
	  ->setFrom(array($send_mail['username'] => '葡萄科技'))
	  ->setTo(array($data['to']))
	  ->setBody($data['data'], 'text/html');

	// Send the message
	$result = $mailer->send($message);
}
<?php

require_once __DIR__ . '/../bootstrap.php';

$config = require_once __DIR__ . '/../config/config.php';

$queue = QueueFactory::create($config);

$queueManager = new QueueManager($queue);

$email = 'email_job_'. rand(1, 99) .'@gmail.com';
$emailJob = new SendEmailJob($email);

$queueManager->dispatch($emailJob);

echo "Job dispatched: $email!\n";
<?php

// Load các interface
require_once __DIR__ . '/app/Jobs/JobInterface.php';
require_once __DIR__ . '/app/Queue/QueueInterface.php';

// Load các Queue class
require_once __DIR__ . '/app/Queue/RedisQueue.php';
require_once __DIR__ . '/app/Queue/MySQLQueue.php';
require_once __DIR__ . '/app/Queue/FileQueue.php';

// Load Queue manager và worker
require_once __DIR__ . '/app/Queue/QueueManager.php';
require_once __DIR__ . '/app/Queue/QueueWorker.php';

//Load Queue factory
require_once __DIR__ . '/app/Queue/QueueFactory.php';

// Load các job
require_once __DIR__ . '/app/Jobs/SendEmailJob.php';

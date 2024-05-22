<?php

require_once __DIR__ . '/../bootstrap.php';

$config = require_once __DIR__ . '/../config/config.php';

$queue = QueueFactory::create($config);

$worker = new QueueWorker($queue);

$worker->work();

echo "Starting worker {$config['queue']['driver']}...\n\n";

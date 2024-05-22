<?php

return [
    'redis' => [
        'host' => 'localhost',
        'port' => 6379,
        'password' => 'kodoku169',
    ],
    'mysql' => [
        'host' => 'localhost',
        'dbname' => 'queue',
        'user' => 'root',
        'password' => '',
    ],
    'file' => [
        'file_name' => 'queue.txt',
    ],
    'queue' => [
        'driver' => 'mysql',
    ]
];

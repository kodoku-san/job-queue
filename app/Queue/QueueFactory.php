<?php 

class QueueFactory
{
    public static function create(array $config) : QueueInterface
    {
        switch ($config['queue']['driver']??null) {
            case 'redis':
                return new RedisQueue($config['redis']['host'], $config['redis']['port'], $config['redis']['password']);
            case 'mysql':
                return new MySQLQueue($config['mysql']['host'], $config['mysql']['dbname'], $config['mysql']['user'], $config['mysql']['password']);
            case 'file':
                return new FileQueue($config['file']['file_name']);
            default:
                throw new Exception('Invalid queue driver');
        }
    }
}
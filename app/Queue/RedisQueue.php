<?php

class RedisQueue implements QueueInterface {
    protected $redis;

    public function __construct(string $host = 'localhost', int $port = 6379, string $password = null) {
        
        try {
            $this->redis = new Redis();
            $this->redis->connect($host, $port);
            $this->redis->auth($password);
        } catch (Exception $e) {
            echo "Error Redis: " . $e->getMessage() . "\n";
        }
    }

    public function push(JobInterface $job) : void 
    {
        $this->redis->rpush('queue:jobs', serialize($job));
    }

    public function pop() : ?JobInterface 
    {
        $job = $this->redis->lpop('queue:jobs');
        return $job ? unserialize($job) : null;
    }
}

<?php

class QueueManager {
    protected $queue;

    public function __construct(QueueInterface $queue) {
        $this->queue = $queue;
    }

    public function dispatch(JobInterface $job) {
        $this->queue->push($job);
    }
}

<?php

class QueueWorker {
    protected $queue;

    public function __construct(QueueInterface $queue) {
        $this->queue = $queue;
    }

    public function work() {
        while (true) {
            $job = $this->queue->pop();
            if ($job) {
                try {
                    $job->handle();
                } catch (Exception $e) {
                    // Xử lý lỗi
                    echo "Error processing job: " . $e->getMessage() . "\n";
                }
            } else {
                // sleep(1);
                usleep(1000000); // 1s
            }
        }
    }
}

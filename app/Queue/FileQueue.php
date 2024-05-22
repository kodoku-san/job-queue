<?php 

class FileQueue implements QueueInterface
{
    private $file_name;

    public function __construct(string $file_name = 'queue.txt')
    {
        $this->file_name = $file_name;
    }

    public function push(JobInterface $job) : void
    {
        $data = serialize($job);
        file_put_contents($this->file_name, $data . PHP_EOL, FILE_APPEND);
    }

    public function pop() : ?JobInterface
    {
        $data = file($this->file_name, FILE_IGNORE_NEW_LINES);

        if (count($data) == 0) return null;
        
        $job = unserialize($data[0]);
        unset($data[0]);
        
        file_put_contents($this->file_name, implode(PHP_EOL, $data));

        return $job;
    }
}
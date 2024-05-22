<?php

interface QueueInterface {
    public function push(JobInterface $job) : void;
    public function pop() : ?JobInterface;
}
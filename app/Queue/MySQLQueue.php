<?php

class MySQLQueue implements QueueInterface {
    protected $pdo;

    public function __construct(string $host, string $dbname, string $user, string $password) {
        
        try {
            
            $dsn = "mysql:host=$host;dbname=$dbname";
            $this->pdo = new PDO($dsn, $user, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            echo "Error MySQL: " . $e->getMessage() . "\n";
        }
    }

    public function push(JobInterface $job) : void
    {
        $stmt = $this->pdo->prepare("INSERT INTO jobs (job) VALUES (:job)");
        $stmt->execute(['job' => serialize($job)]);
    }

    public function pop() : ?JobInterface
    {
        $this->pdo->beginTransaction();

        $stmt = $this->pdo->prepare("SELECT * FROM jobs ORDER BY id ASC LIMIT 1 FOR UPDATE");
        $stmt->execute();
        $jobRecord = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($jobRecord) {
            $stmt = $this->pdo->prepare("DELETE FROM jobs WHERE id = :id");
            $stmt->execute(['id' => $jobRecord['id']]);
            $this->pdo->commit();
            return unserialize($jobRecord['job']);
        } else {
            $this->pdo->rollBack();
            return null;
        }
    }
}

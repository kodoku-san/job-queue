<?php

class SendEmailJob implements JobInterface {
    protected $email;

    public function __construct($email) {
        $this->email = $email;
    }

    public function handle() {

        $s = rand(1, 5);
        echo "Sending email to " . $this->email . "...\n";
        sleep($s);
        echo "Email sent to " . $this->email . " ({$s}s)\n\n";

    }
}

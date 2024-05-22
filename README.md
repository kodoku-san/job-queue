> Author: KODOKU
>
> Github: [@kodoku-san](https://github.com/kodoku-san)
---

# Tạo Queue Job Bằng PHP Thuần

## Giới thiệu

Queue job là một phần quan trọng trong việc xử lý các công việc nền (background jobs) trong ứng dụng web. Các công việc nền thường là những công việc mà không cần phản hồi ngay lập tức từ phía server, giúp tăng hiệu suất và giảm thời gian phản hồi cho người dùng.

Để tạo một hệ thống queue tương tự như framework Laravel bằng PHP thuần, bạn sẽ cần thiết lập các thành phần sau:

1. **Job Class**: Một lớp đại diện cho công việc cần thực hiện.
2. **Queue System**: Hệ thống lưu trữ các job (có thể sử dụng cơ sở dữ liệu, Redis, hay bất kỳ hệ thống lưu trữ nào khác).
3. **Queue Manager**: Quản lý việc đẩy job vào queue và lấy job ra để xử lý.
4. **Queue Worker**: Worker xử lý các job trong queue.
5. **Command Line Interface (CLI)**: Tương tự như Artisan của Laravel, để khởi động và quản lý worker.


Để tổ chức mã nguồn một cách hợp lý, bạn nên chia thành các thư mục rõ ràng để dễ quản lý và mở rộng trong tương lai. Dưới đây là một cấu trúc thư mục sẽ dùng để tạo queue job bằng PHP thuần:

```
project/
├── app/
│   ├── Jobs/
│   │   ├── JobInterface.php
│   │   ├── SendEmailJob.php
│   ├── Queue/
│   │   ├── RedisQueue.php
│   │   ├── QueueManager.php
│   │   ├── QueueWorker.php
│   │   ├── QueueInterface.php
│   │   ├── QueueFactory.php
├── config/
│   ├── config.php
├── scripts/
│   ├── add_job.php
│   ├── worker.php
├── bootstrap.php
├── vendor/ // nếu dùng composer
├── composer.json
└── composer.lock
```

### 1. Job interface và các Job Class

Tạo một interface cho các job và một lớp cụ thể thực hiện job.

**JobInterface.php**

```php
interface JobInterface {
    public function handle();
}
```

Tạo một job cụ thể, ví dụ: gửi email.
- Các class job sẽ implement `JobInterface` và thực hiện phương thức `handle()`. 

**SendEmailJob.php**

```php
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
```

- Phương thức `handle()` sẽ thực hiện công việc gửi email demo.

### 2. Queue Interface

Interface cho hệ thống queue.

**QueueInterface.php**

```php
interface QueueInterface {
    public function push(JobInterface $job) : void;
    public function pop() : ?JobInterface;
}
```

- Interface này sẽ có hai phương thức chính là `push()` và `pop()`.
- `push()`: Thêm một job vào queue.
- `pop()`: Lấy một job từ queue và trả về `null` nếu không có job nào.

#### Chúng ta sẽ sử dụng Redis, MySQL, File để lưu trữ job cho queue.

**RedisQueue.php**

```php
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
```

- `RedisQueue` sử dụng Redis để lưu trữ các job. 
- Class này sẽ implement `QueueInterface` và thực hiện phương thức `push()` và `pop()`.
- Phương thức `push()` sẽ thêm một job vào queue bằng cách sử dụng `rpush()` của Redis.
  - `rpush()` sẽ thêm một giá trị vào cuối list.
- Phương thức `pop()` sẽ lấy một job từ queue bằng cách sử dụng `lpop()` của Redis.
  - `lpop()` sẽ lấy giá trị đầu tiên của list và xóa nó khỏi list.

> Tương tự như Redis, bạn cũng có thể tạo MySQLQueue, FileQueue, hoặc bất kỳ hệ thống lưu trữ nào khác dựa trên nhu cầu cụ thể.

### 3. Queue Manager

Quản lý việc thêm job vào queue.

**QueueManager.php**

```php
class QueueManager {
    protected $queue;

    public function __construct(QueueInterface $queue) {
        $this->queue = $queue;
    }

    public function dispatch(JobInterface $job) {
        $this->queue->push($job);
    }
}
```

- `QueueManager` nhận một instance của class queue thông qua constructor.
- Phương thức `dispatch()` sử dụng `push()` để thêm job vào queue.

### 4. Queue Worker

Worker để xử lý các job trong queue. (Gọi phương thức `handle()` của job)

**QueueWorker.php**

```php
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
```

- Worker liên tục lắng nghe và xử lý các job trong queue.
- Nếu có job, worker sẽ lấy job ra và thực hiện phương thức `handle()`.
- Nếu không có job, worker sẽ chờ 1 giây rồi kiểm tra lại.

**QueueFactory.php**

```php
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
```

- `QueueFactory::create()` sử dụng `switch` để tạo queue dựa trên driver trong config.
- Sau đó trả về một instance của class queue tương ứng.

### 5. Load Job và Queue

Tiếp theo, load các class vào bootstrap file.

**bootstrap.php**

```php
// Load các interface
require_once __DIR__ . '/app/Jobs/JobInterface.php';
require_once __DIR__ . '/app/Queue/QueueInterface.php';

// Load các Queue class
require_once __DIR__ . '/app/Queue/RedisQueue.php';
require_once __DIR__ . '/app/Queue/MySQLQueue.php';
require_once __DIR__ . '/app/Queue/FileQueue.php';

// Load Queue manager và worker
require_once __DIR__ . '/app/Queue/QueueManager.php';
require_once __DIR__ . '/app/Queue/QueueWorker.php';

//Load Queue factory
require_once __DIR__ . '/app/Queue/QueueFactory.php';

// Load các job
require_once __DIR__ . '/app/Jobs/SendEmailJob.php';
```

- File `bootstrap.php` sẽ load tất cả các class cần thiết cho hệ thống queue.

#### Ngoài ra cũng có thể sử dụng `composer` để load các class.
1. Tạo file `composer.json` và thêm nội dung sau:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    }
}
```

2. Chạy `composer install` để cài đặt và dump autoload.

```bash
composer dump-autoload
```

3. Thêm `require 'vendor/autoload.php';` vào file `bootstrap.php`.

```php
require 'vendor/autoload.php';
```

4. Sau cùng, thêm `namespace` cho các `class` sao cho phù hợp với cấu trúc thư mục.


#### Tạo config cho hệ thống queue.

**config.php**

```php 
return [
    'redis' => [
        'host' => 'localhost',
        'port' => 6379,
        'password' => '...',
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
        'driver' => 'redis',
    ]
];
```

### 6. Command Line Interface (CLI)

Tạo một script đơn giản để khởi động worker.

**worker.php**

```php
require_once __DIR__ . '/../bootstrap.php';

$config = require_once __DIR__ . '/../config/config.php';

$queue = QueueFactory::create($config);

$worker = new QueueWorker($queue);

echo "Starting worker {$config['queue']['driver']}...\n\n";
$worker->work();
```

- Script này sẽ khởi động worker và xử lý các job trong queue.
- Sử dụng `QueueFactory::create($config)` để tạo queue dựa trên config.
- Sử dụng `QueueWorker()->work()` để xử lý các job trong queue.

Tạo một script đơn giản để thêm job vào queue.

**add_job.php**

```php
require_once __DIR__ . '/../bootstrap.php';

$config = require_once __DIR__ . '/../config/config.php';

$queue = QueueFactory::create($config);

$queueManager = new QueueManager($queue);

$email = 'email_job_'. rand(1, 99) .'@gmail.com';
$emailJob = new SendEmailJob($email);

$queueManager->dispatch($emailJob);

echo "Job dispatched: $email!\n";
```

- Script này sẽ thêm một job vào queue.
- Sử dụng `QueueFactory::create($config)` để tạo queue dựa trên config.
- Sử dụng `QueueManager->dispatch($job)` để thêm job vào queue.
- Job sẽ được thêm vào queue và worker sẽ xử lý nó.

### Chạy Hệ Thống Queue

1. **Thêm Mới Job**: `php scripts/add_job.php`.
2. **Chạy Worker**: `php scripts/worker.php` để bắt đầu worker và xử lý các job trong queue.

## Kết luận

Với hệ thống này, bạn đã tạo một queue job tương tự như Laravel bằng PHP thuần. Bạn có thể mở rộng và tối ưu hóa thêm dựa trên nhu cầu cụ thể của mình. 

Tuy nhiên, để triển khai trong môi trường thực tế, bạn cần xem xét các yếu tố như quản lý lỗi, bảo mật, và hiệu suất. Chúc bạn thành công!

`KODOKU`
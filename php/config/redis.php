<?php
require_once __DIR__ . '/env_loader.php';

// php/config/redis.php
define('REDIS_HOST', $_ENV['REDIS_HOST'] ?? getenv('REDIS_HOST') ?: 'redis-18929.crce182.ap-south-1-1.ec2.cloud.redislabs.com');
define('REDIS_PORT', (int)($_ENV['REDIS_PORT'] ?? getenv('REDIS_PORT') ?: 18929));
define('REDIS_PASS', $_ENV['REDIS_PASS'] ?? getenv('REDIS_PASS') ?: 'G6Dmmh9dkCPBPmNYYcVbqC9ayV2Y7LiY');
define('SESSION_TTL', 86400);

/**
 * Redis Handler
 * 1. Try phpredis extension (fastest)
 * 2. Try Predis (robust, works on Vercel)
 * 3. Fallback to Local Mock (for local dev without Redis)
 */

$redis = null;

// Option 1: PHPRedis Extension
if (extension_loaded('redis')) {
    try {
        $redis = new Redis();
        if (@$redis->connect(REDIS_HOST, REDIS_PORT)) {
            if (REDIS_PASS) @$redis->auth(REDIS_PASS);
        } else {
            $redis = null;
        }
    } catch (Throwable $e) { $redis = null; }
}

// Option 2: Predis (Composer package)
if (!$redis && class_exists('Predis\Client')) {
    try {
        $redis = new Predis\Client([
            'scheme' => 'tcp',
            'host'   => REDIS_HOST,
            'port'   => REDIS_PORT,
            'password' => REDIS_PASS ?: null,
            'timeout'  => 2.0
        ]);
        $redis->ping(); // Verify connection
    } catch (Throwable $e) { $redis = null; }
}

// Option 3: Local File-Based Mock (Emergency fallback)
if (!$redis) {
    if (!class_exists('RedisLocalMock')) {
        class RedisLocalMock {
            public $isMock = true;
            private $storageFile;
            public function __construct() {
                $this->storageFile = sys_get_temp_dir() . '/organic_ink_redis.json';
                if (!file_exists($this->storageFile)) @file_put_contents($this->storageFile, json_encode([]));
            }
            private function load() { return json_decode(@file_get_contents($this->storageFile), true) ?: []; }
            private function save($d) { @file_put_contents($this->storageFile, json_encode($d)); }
            public function set($k, $v, $ttl = 86400) {
                $d = $this->load(); $d[$k] = ['v' => $v, 'e' => time() + $ttl]; $this->save($d); return true;
            }
            public function get($k) {
                $d = $this->load();
                if (isset($d[$k]) && $d[$k]['e'] > time()) return $d[$k]['v'];
                return false;
            }
            public function del($k) { $d = $this->load(); unset($d[$k]); $this->save($d); return true; }
            public function exists($k) { return $this->get($k) !== false; }
        }
    }
    $redis = new RedisLocalMock();
}

return $redis;

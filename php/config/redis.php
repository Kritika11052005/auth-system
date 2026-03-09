<?php
require_once __DIR__ . '/env_loader.php';

// php/config/redis.php
define('REDIS_HOST', $_ENV['REDIS_HOST'] ?? getenv('REDIS_HOST') ?: 'redis-18929.crce182.ap-south-1-1.ec2.cloud.redislabs.com');
define('REDIS_PORT', (int)($_ENV['REDIS_PORT'] ?? getenv('REDIS_PORT') ?: 18929));
define('REDIS_PASS', $_ENV['REDIS_PASS'] ?? getenv('REDIS_PASS') ?: 'G6Dmmh9dkCPBPmNYYcVbqC9ayV2Y7LiY');
define('SESSION_TTL', 86400);

/**
 * Handle missing Redis extension gracefully for local development.
 * In a production environment like Vercel, the extension is typically pre-installed.
 * This mock uses a temporary file-based storage to allow session persistence across requests.
 */
if (!class_exists('Redis')) {
    class Redis {
        public $isMock = true;
        private $storageFile;

        public function __construct() {
            $this->storageFile = sys_get_temp_dir() . '/organic_ink_redis_mock.json';
            if (!file_exists($this->storageFile)) {
                @file_put_contents($this->storageFile, json_encode([]));
            }
        }

        private function load() {
            if (!file_exists($this->storageFile)) return [];
            return json_decode(@file_get_contents($this->storageFile), true) ?: [];
        }

        private function save($data) {
            @file_put_contents($this->storageFile, json_encode($data));
        }

        public function connect($h, $p) { return true; }
        public function auth($p) { return true; }
        
        public function set($k, $v, $ttl = 86400) {
            $data = $this->load();
            $data[$k] = [
                'value' => $v,
                'expires' => time() + $ttl
            ];
            $this->save($data);
            return true;
        }

        public function get($k) {
            $data = $this->load();
            if (isset($data[$k])) {
                if ($data[$k]['expires'] > time()) {
                    return $data[$k]['value'];
                }
                $this->del($k); // Cleanup expired
            }
            return false;
        }

        public function del($k) {
            $data = $this->load();
            unset($data[$k]);
            $this->save($data);
            return true;
        }

        public function exists($k) {
            return $this->get($k) !== false;
        }
    }
}

$redis = new Redis();

// Check if we are using the real Redis extension before attempting connection
if (extension_loaded('redis') && !isset($redis->isMock)) {
    try {
        if (!@$redis->connect(REDIS_HOST, REDIS_PORT)) {
            // Connection failed but we have the instance
        }
        if (REDIS_PASS) {
            @$redis->auth(REDIS_PASS);
        }
    } catch (Exception $e) {
        // Silently catch connection errors in dev
    }
}

return $redis;

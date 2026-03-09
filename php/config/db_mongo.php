<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/env_loader.php';

use MongoDB\Client;

/**
 * php/config/db_mongo.php
 * Handles MongoDB Atlas connection with a robust mock fallback for local development.
 * If the TLS handshake fails (common on Windows), it switches to local JSON storage.
 */

// 1. Define Mock Classes to mimic MongoDB Driver behavior
if (!class_exists('MongoDBMockResult')) {
    class MongoDBMockResult {
        private $upserted;
        public function __construct($upserted = 0) { $this->upserted = $upserted; }
        public function getUpsertedCount() { return $this->upserted; }
        public function getModifiedCount() { return 1; }
    }

    class MongoDBMockCollection {
        private $storageFile;
        public function __construct() {
            $this->storageFile = sys_get_temp_dir() . '/organic_ink_mongo_mock.json';
            if (!file_exists($this->storageFile)) {
                @file_put_contents($this->storageFile, json_encode([]));
            }
        }
        private function load() {
            return json_decode(@file_get_contents($this->storageFile), true) ?: [];
        }
        private function save($data) {
            @file_put_contents($this->storageFile, json_encode($data));
        }
        public function findOne($filter) {
            $data = $this->load();
            foreach ($data as $doc) {
                if (isset($filter['user_id']) && $doc['user_id'] == $filter['user_id']) return $doc;
            }
            return null;
        }
        public function updateOne($filter, $update, $options = []) {
            $data = $this->load();
            $found = false;
            foreach ($data as &$doc) {
                if (isset($filter['user_id']) && $doc['user_id'] == $filter['user_id']) {
                    $doc = array_merge($doc, $update['$set']);
                    $found = true;
                    break;
                }
            }
            if (!$found && isset($options['upsert']) && $options['upsert']) {
                $newDoc = array_merge(['user_id' => $filter['user_id']], $update['$set']);
                $data[] = $newDoc;
                $this->save($data);
                return new MongoDBMockResult(1);
            }
            $this->save($data);
            return new MongoDBMockResult(0);
        }
    }
}

$rawUri = $_ENV['MONGO_URI'] ?? getenv('MONGO_URI') ?: 'mongodb+srv://ananyabenjwal_db_user:Vp9QjWTveoXbKgpd@cluster0.8oihqhp.mongodb.net/guvi_intern?appName=Cluster0';
$dbName = $_ENV['MONGO_DB'] ?? getenv('MONGO_DB') ?: 'guvi_intern';
$collName = $_ENV['MONGO_COLL'] ?? getenv('MONGO_COLL') ?: 'profiles';

try {
    // 2. Check if MongoDB extension exists
    if (!extension_loaded('mongodb')) {
        throw new RuntimeException("MongoDB extension not loaded.");
    }

    // Attempt real connection
    $client = new Client($rawUri, [
        'tls' => true,
        'tlsAllowInvalidCertificates' => true, 
        'tlsAllowInvalidHostnames' => true,
        'serverSelectionTimeoutMS' => 2000,
        'connectTimeoutMS' => 2000
    ]);
    
    $database = $client->selectDatabase($dbName);
    $collection = $database->selectCollection($collName);

    // Verify connection
    $database->command(['ping' => 1]);

    return [
        'client' => $client,
        'database' => $database,
        'collection' => $collection,
        'isMock' => false
    ];

} catch (Throwable $t) {
    // Fallback if extension missing or connection fails
    $mockCollection = new MongoDBMockCollection();
    return [
        'client' => null,
        'database' => null,
        'collection' => $mockCollection,
        'isMock' => true,
        'error' => $t->getMessage()
    ];
}

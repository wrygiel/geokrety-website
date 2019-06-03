<?php

namespace Consistency;

/**
 * RedisClient : redis read write access
 */
class RedisClient {
    private static $_instance = null;

    private $redisHost = "redis";
    private $redisPort = 6379;

    private $link = null;

    private function __construct() {
    }

    public static function getInstance($config) {
        // TODO $config to handle
        if (is_null(self::$_instance)) {
            self::$_instance = new RedisClient();
        }

        return self::$_instance;
    }

    public function connect() {
        if ($this->link != null) {
                return $this->link;
        }
        $this->link = new \Redis();
        $this->link->connect($this->redisHost, $this->redisPort);
        echo "Connection to REDIS $this->redisHost:$this->redisPort successfully<br/>\n";
        return $this->link;
    }

    public function get($redisKey) {
        return $this->link->get($redisKey);
    }

    public function set($redisKey, $value) {
        return $this->link->set($redisKey, $value);
    }

    public function getFromRedis($rollId, $gkId) {
        $redisKey = $this->buildRedisKey($rollId, $gkId);
        $jsonObject = $this->link->get($redisKey);
        return json_decode($jsonObject, true);
    }

    public function putInRedis($rollId, $gkId, $geokretyObject, $ttlSec) {
        $redisKey = $this->buildRedisKey($rollId, $gkId);
        $jsonObject = json_encode($geokretyObject);
        $this->link->set($redisKey, $jsonObject, $this->redisValueTimeToLiveSec);
    }

    private function buildRedisKey($rollId, $gkId) {
        return "gkm-roll-$rollId-gkid-$gkId";
    }
}
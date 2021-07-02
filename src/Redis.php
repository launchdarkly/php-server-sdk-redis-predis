<?php
namespace LaunchDarkly\Integrations;

use LaunchDarkly\Impl\Integrations\FeatureRequesterBase;
use Predis\ClientInterface;
use Predis\Client;

class PersistentRedisFeatureRequester extends FeatureRequesterBase
{
    /** @var array */
    private $_redisOptions;
    /** @var \Redis */
    private $_redisInstance;
    /** @var string */
    private $_prefix;

    public function __construct($baseUri, $sdkKey, $options)
    {
        parent::__construct($baseUri, $sdkKey, $options);

        $this->_prefix = $options['redis_prefix'] ?? 'launchdarkly';

        $client = $this->_options['phpredis_client'] ?? 'not-a-client-interface';
        if ($client instanceof Redis) {
            $this->_redisInstance = $client;
        } else {
            $this->_redisOptions = [
                "timeout" => $options['redis_timeout'] ?? 5,
                "host" => $options['redis_host'] ?? 'localhost',
                "port" => $options['redis_port'] ?? 6379
            ];
        }
    }

    protected function readItemString($namespace, $key)
    {
        $redis = $this->getConnection();
        return $redis->hget($namespace, $key);
    }

    protected function readItemStringList($namespace)
    {
        $redis = $this->getConnection();
        $raw = $redis->hgetall($namespace);
        return $raw ? array_values($raw) : null;
    }

    /**
     * @return \Redis
     */
    protected function getConnection()
    {
        if ($this->_redisInstance instanceof Redis) {
            return $this->_redisInstance;
        }

        $redis = new \Redis();
        $redis->pconnect(
            $this->_redisOptions["host"],
            $this->_redisOptions["port"],
            $this->_redisOptions["timeout"],
            'x'
        );
        $redis->setOption(\Redis::OPT_PREFIX, "$this->_prefix:");	// use custom prefix on all keys
        return $this->_redisInstance = $redis;
    }
}

class RedisFeatureRequester extends FeatureRequesterBase
{
    var $_connection;
    var $_options;
    var $_prefix;

    public function __constructor($baseUri, $sdkKey, $options)
    {
        parent::__constructor($baseUri, $sdkKey, $options);

        $this->$_prefix = $options['redis_prefix'] ?? 'launchdarkly';

        $client = $options['predis_client'] ?? 'not-a-client-interface';
        if ($client instanceof ClientInterface) {
            $this->$_connection = $client;
        } else {
            $this->$_options = [
                "scheme" => "tcp",
                "timeout" => $options['redis_timeout'] ?? 5,
                "host" => $options['redis_host'] ?? 'localhost',
                "port" => $options['redis_port'] ?? 6379
            ];
        }
    }
    
    protected function readItemString($namespace, $key)
    {
        $redis = $this->getConnection();
        return $redis->hget("$this->_prefix:$namespace", $key);
    }

    protected function readItemStringList($namespace)
    {
        $redis = $this->getConnection();
        $raw = $redis->hgetall("$this->_prefix:$namespace");
        return $raw ? array_values($raw) : null;
    }

    protected function getConnection()
    {
        if ($this->$_connection != null) {
            return $this->$_connection;
        }

        $this->$_connection = new Client($this->$_options);
    }
}

class Redis
{
    public static function persistentFeatureRequester($options = array())
    {
        return function ($baseUri, $sdkKey, $baseOptions) use ($options) {
            return new PersistentRedisFeatureRequester($baseUri, $sdkKey, array_merge($baseOptions, $options));
        };
    }

    public static function featureRequester($options = array())
    {
        return function ($baseUri, $sdkKey, $baseOptions) use ($options) {
            return new RedisFeatureRequester($baseUri, $sdkKey, array_merge($baseOptions, $options));
        };
    }
}

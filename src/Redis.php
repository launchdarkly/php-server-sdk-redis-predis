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
    /**
     * Configures an adapter for reading feature flag data from Redis using persistent connections.
     *
     * To use this method, you must have installed the `phpredis` extension. After calling this
     * method, store its return value in the `feature_requester` property of your client configuration:
     *
     *     $fr = LaunchDarkly\Integrations\PHPRedis::featureRequester([ "redis_prefix" => "env1" ]);
     *     $config = [ "feature_requester" => $fr ];
     *     $client = new LDClient("sdk_key", $config);
     *
     * For more about using LaunchDarkly with databases, see the
     * [SDK reference guide](https://docs.launchdarkly.com/v2.0/docs/using-a-persistent-feature-store).
     *
     * @param array $options  Configuration settings (can also be passed in the main client configuration):
     *   - `redis_host`: hostname of the Redis server; defaults to `localhost`
     *   - `redis_port`: port of the Redis server; defaults to 6379
     *   - `redis_timeout`: connection timeout in seconds; defaults to 5
     *   - `redis_prefix`: a string to be prepended to all database keys; corresponds to the prefix
     * setting in ld-relay
     *   - `phpredis_client`: an already-configured Predis client instance if you wish to reuse one
     *   - `apc_expiration`: expiration time in seconds for local caching, if `APCu` is installed
     * @return mixed  an object to be stored in the `feature_requester` configuration property
     */
    public static function persistentFeatureRequester($options = array())
    {
        return function ($baseUri, $sdkKey, $baseOptions) use ($options) {
            return new PersistentRedisFeatureRequester($baseUri, $sdkKey, array_merge($baseOptions, $options));
        };
    }

    /**
     * Configures an adapter for reading feature flag data from Redis.
     *
     * To use this method, you must have installed the package `predis/predis`. After calling this
     * method, store its return value in the `feature_requester` property of your client configuration:
     *
     *     $fr = LaunchDarkly\Integrations\Redis::featureRequester([ "redis_prefix" => "env1" ]);
     *     $config = [ "feature_requester" => $fr ];
     *     $client = new LDClient("sdk_key", $config);
     *
     * For more about using LaunchDarkly with databases, see the
     * [SDK reference guide](https://docs.launchdarkly.com/v2.0/docs/using-a-persistent-feature-store).
     *
     * @param array $options  Configuration settings (can also be passed in the main client configuration):
     *   - `redis_host`: hostname of the Redis server; defaults to `localhost`
     *   - `redis_port`: port of the Redis server; defaults to 6379
     *   - `redis_timeout`: connection timeout in seconds; defaults to 5
     *   - `redis_prefix`: a string to be prepended to all database keys; corresponds to the prefix
     * setting in ld-relay
     *   - `predis_client`: an already-configured Predis client instance if you wish to reuse one
     *   - `apc_expiration`: expiration time in seconds for local caching, if `APCu` is installed
     * @return mixed  an object to be stored in the `feature_requester` configuration property
     */
    public static function featureRequester($options = array())
    {
        return function ($baseUri, $sdkKey, $baseOptions) use ($options) {
            return new RedisFeatureRequester($baseUri, $sdkKey, array_merge($baseOptions, $options));
        };
    }
}

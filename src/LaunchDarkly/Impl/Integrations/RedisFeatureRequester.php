<?php
namespace LaunchDarkly\Impl\Integrations;

use LaunchDarkly\Impl\Integrations\FeatureRequesterBase;
use Predis\ClientInterface;
use Predis\Client;

class RedisFeatureRequester extends FeatureRequesterBase
{
    /** @var ClientInterface */
    var $_connection;
    /** @var array */
    var $_redisOptions;
    /** @var string */
    var $_prefix;

    const DEFAULT_PREFIX = 'launchdarkly';

    public function __construct(string $baseUri, string $sdkKey, array $options)
    {
        parent::__construct($baseUri, $sdkKey, $options);

        $this->_prefix = $options['redis_prefix'] ?? null;
        if ($this->_prefix === null || $this->_prefix === '') {
            $this->_prefix = self::DEFAULT_PREFIX;
        }

        $client = $options['predis_client'] ?? null;
        if ($client instanceof ClientInterface) {
            $this->_connection = $client;
        } else {
            $this->_redisOptions = [
                "scheme" => "tcp",
                "timeout" => $options['redis_timeout'] ?? 5,
                "host" => $options['redis_host'] ?? 'localhost',
                "port" => $options['redis_port'] ?? 6379
            ];

            $this->_redisOptions = array_merge($this->_redisOptions, $options['predis_options'] ?? []);
        }
    }

    protected function readItemString(string $namespace, string $key): ?string
    {
        $redis = $this->getConnection();
        return $redis->hget("$this->_prefix:$namespace", $key);
    }

    protected function readItemStringList(string $namespace): ?array
    {
        $redis = $this->getConnection();
        $raw = $redis->hgetall("$this->_prefix:$namespace");
        return $raw ? array_values($raw) : null;
    }

    protected function getConnection(): ClientInterface
    {
        if ($this->_connection == null) {
            $this->_connection = new Client($this->_redisOptions);
        }

        return $this->_connection;
    }
}

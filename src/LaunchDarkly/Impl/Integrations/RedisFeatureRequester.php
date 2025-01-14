<?php

namespace LaunchDarkly\Impl\Integrations;

use LaunchDarkly\Integrations;
use Predis\ClientInterface;

class RedisFeatureRequester extends FeatureRequesterBase
{
    private readonly string $prefix;

    /**
     * @param array<string,mixed> $options
     *   - `prefix`: namespace prefix to add to all hash keys
     */
    public function __construct(
        private readonly ClientInterface $connection,
        string $baseUri,
        string $sdkKey,
        array $options
    ) {
        parent::__construct($baseUri, $sdkKey, $options);
        $this->prefix = $options['prefix'] ?? Integrations\Redis::DEFAULT_PREFIX;
    }

    protected function readItemString(string $namespace, string $key): ?string
    {
        return $this->connection->hget("$this->prefix:$namespace", $key);
    }

    protected function readItemStringList(string $namespace): ?array
    {
        $raw = $this->connection->hgetall("$this->prefix:$namespace");
        return $raw ? array_values($raw) : null;
    }
}

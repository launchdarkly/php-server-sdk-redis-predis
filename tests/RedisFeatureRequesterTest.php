<?php

namespace LaunchDarkly\Impl\Integrations\Tests;

use LaunchDarkly\Integrations\Redis;
use LaunchDarkly\SharedTest\DatabaseFeatureRequesterTestBase;
use Predis\Client;

class RedisFeatureRequesterTest extends DatabaseFeatureRequesterTestBase
{
    /** @var ClientInterface */
    private static $predisClient;

    public static function setUpBeforeClass(): void
    {
        self::$predisClient = new Client(array());
    }

    protected function clearExistingData($prefix): void
    {
        $p = self::realPrefix($prefix);
        $keys = self::$predisClient->keys("$p:*");
        foreach ($keys as $key) {
            self::$predisClient->del($key);
        }
    }

    protected function makeRequester($prefix)
    {
        $factory = Redis::featureRequester(['redis_prefix' => $prefix]);
        return $factory('', '', []);
    }

    protected function putSerializedItem($prefix, $namespace, $key, $version, $json): void
    {
        $p = self::realPrefix($prefix);
        self::$predisClient->hset("$p:$namespace", $key, $json);
    }

    private static function realPrefix($prefix)
    {
        if ($prefix === null || $prefix === '') {
            return 'launchdarkly';
        }
        return $prefix;
    }
}

<?php

namespace LaunchDarkly\Impl\Integrations\Tests;

use LaunchDarkly\Impl\Integrations\RedisFeatureRequester;
use LaunchDarkly\Integrations\Redis;
use LaunchDarkly\SharedTest\DatabaseFeatureRequesterTestBase;
use Predis\Client;

class RedisFeatureRequesterWithClientTest extends DatabaseFeatureRequesterTestBase
{
    const CLIENT_PREFIX = 'clientprefix';

    /** @var ClientInterface */
    private static $predisClient;

    public static function setUpBeforeClass(): void
    {
        self::$predisClient = new Client([], [
            'prefix' => self::CLIENT_PREFIX
        ]);
        // Setting a prefix parameter on the Predis\Client causes it to prepend
        // that string to every key *in addition to* the other prefix that the SDK
        // integration is applying. This is done transparently so we do not need
        // to add CLIENT_PREFIX in putItem below. We're doing it so we can be sure
        // that the RedisFeatureRequester really is using the same client we
        // passed to it; if it didn't, the tests would fail because putItem was
        // creating keys with both prefixes but RedisFeatureRequester was looking
        // for keys with only one prefix.
    }

    protected function clearExistingData($prefix): void
    {
        $p = self::realPrefix($prefix);
        $keys = self::$predisClient->keys("$p:*");
        foreach ($keys as $key) {
            if (substr($key, 0, strlen(self::CLIENT_PREFIX)) === self::CLIENT_PREFIX) {
                // remove extra prefix from the queried keys because del() will re-add it
                $key = substr($key, strlen(self::CLIENT_PREFIX));
            }
            self::$predisClient->del($key);
        }
    }

    protected function makeRequester($prefix)
    {
        $factory = Redis::featureRequester([
            'redis_prefix' => $prefix,
            'predis_client' => self::$predisClient
        ]);
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

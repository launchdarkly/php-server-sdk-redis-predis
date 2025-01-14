<?php

namespace LaunchDarkly\Integrations;

use LaunchDarkly\Impl\Integrations\RedisBigSegmentsStore;
use LaunchDarkly\Impl\Integrations\RedisFeatureRequester;
use LaunchDarkly\Subsystems;
use Predis\ClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Integration with a Redis data store using the `predis` package.
 */
class Redis
{
    const DEFAULT_PREFIX = 'launchdarkly';

    /**
     * Configures an adapter for reading feature flag data from Redis.
     *
     * After calling this method, store its return value in the `feature_requester` property of your client configuration:
     *
     *     $fr = LaunchDarkly\Integrations\Redis::featureRequester(["prefix" => "env1"]);
     *     $config = ["feature_requester" => $fr];
     *     $client = new LDClient("sdk_key", $config);
     *
     * For more about using LaunchDarkly with databases, see the
     * [SDK reference guide](https://docs.launchdarkly.com/sdk/features/storing-data).
     *
     * @param array<string, mixed> $options Configuration settings (can also be passed in the main client configuration):
     *   - `prefix`: a string to be prepended to all database keys; corresponds
     *   to the prefix setting in ld-relay
     * @return callable(string, string, array): Subsystems\FeatureRequester
     */
    public static function featureRequester(ClientInterface $client, array $options = []): callable
    {
        return function (string $baseUri, string $sdkKey, array $baseOptions) use ($client, $options): Subsystems\FeatureRequester {
            return new RedisFeatureRequester($client, $baseUri, $sdkKey, array_merge($baseOptions, $options));
        };
    }

    /**
     * Configures a big segments store instance backed by Redis.
     *
     * After calling this method, store its return value in the `store` property of your Big Segment configuration:
     *
     *     $store = LaunchDarkly\Integrations\Redis::bigSegmentsStore(["prefix" => "env1"]);
     *     $bigSegmentsConfig = new LaunchDarkly\BigSegmentConfig(store: $store);
     *     $config = ["big_segments" => $bigSegmentsConfig];
     *     $client = new LDClient("sdk_key", $config);
     *
     * For more about using LaunchDarkly with databases, see the
     * [SDK reference guide](https://docs.launchdarkly.com/sdk/features/storing-data).
     *
     * @param array<string,mixed> $options
     *   - `prefix`: a string to be prepended to all database keys; corresponds
     *   to the prefix setting in ld-relay
     * @return callable(LoggerInterface, array): Subsystems\BigSegmentsStore
     */
    public static function bigSegmentsStore(ClientInterface $client, array $options = []): callable
    {
        return function (LoggerInterface $logger, array $baseOptions) use ($client, $options): Subsystems\BigSegmentsStore {
            return new RedisBigSegmentsStore($client, $logger, array_merge($baseOptions, $options));
        };
    }
}

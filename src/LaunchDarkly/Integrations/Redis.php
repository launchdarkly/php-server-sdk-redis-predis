<?php
namespace LaunchDarkly\Integrations;

use \LaunchDarkly\Impl\Integrations\RedisFeatureRequester;

/**
 * Integration with a Redis data store using the `predis` package.
 */
class Redis
{
    /**
     * Configures an adapter for reading feature flag data from Redis.
     *
     * After calling this method, store its return value in the `feature_requester` property of your client configuration:
     *
     *     $fr = LaunchDarkly\Integrations\Redis::featureRequester([ "redis_prefix" => "env1" ]);
     *     $config = [ "feature_requester" => $fr ];
     *     $client = new LDClient("sdk_key", $config);
     *
     * For more about using LaunchDarkly with databases, see the
     * [SDK reference guide](https://docs.launchdarkly.com/sdk/features/storing-data).
     *
     * @param array $options  Configuration settings (can also be passed in the main client configuration):
     *   - `redis_host`: hostname of the Redis server; defaults to `localhost`
     *   - `redis_port`: port of the Redis server; defaults to 6379
     *   - `redis_timeout`: connection timeout in seconds; defaults to 5
     *   - `redis_prefix`: a string to be prepended to all database keys; corresponds to the prefix
     * setting in ld-relay
     *   - `predis_client`: an already-configured Predis client instance if you wish to reuse one; if
     * specified, this will cause all other options except `redis_prefix` and `apc_expiration`
     * to be ignored
     *   - `predis_options`: an array of connection options to be passed directly to the Predis client;
     * these can include any options supported by `Predis\Client`, and will override any equivalent
     * top-level options (for instance, `"host"` in `predis_options` takes priority over `"redis_host"`
     * at the top level)
     *   - `apc_expiration`: expiration time in seconds for local caching, if `APCu` is installed
     * @return mixed  an object to be stored in the `feature_requester` configuration property
     */
    public static function featureRequester(array $options = [])
    {
        return function (string $baseUri, string $sdkKey, array $baseOptions) use ($options) {
            return new RedisFeatureRequester($baseUri, $sdkKey, array_merge($baseOptions, $options));
        };
    }
}

# LaunchDarkly SDK for PHP - Redis integration

## Quick setup

This assumes that you already have the LaunchDarkly PHP SDK version >=4 installed.

1. Add this library to your project

```sh
composer require launchdarkly/server-sdk-redis
```

2. The Redis client library (predis) should be pulled in automatically. If you want to use a different version, you may add your own version

```json
"require": {
    "predis/predis": "some.other.version"
}
```

3. Import the LaunchDarkly package and the package for this library

```php
use LaunchDarkly\LDClient;
use LaunchDarkly\Integrations\Redis;
```

4. When configuring your SDK client, add the Redis data store.

```php
$client = new LDClient("YOUR_SDK_KEY", [
    'feature_requester' => Redis::featureRequester()
]);

// or

$client = new LDClient("YOUR_SDK_KEY", [
    'feature_requester' => Redis::presistentFeatureRequester()
]);
```
# Change log

All notable changes to the project will be documented in this file. This project adheres to [Semantic Versioning](http://semver.org).

## [2.0.0](https://github.com/launchdarkly/php-server-sdk-redis-predis/compare/1.3.0...2.0.0) (2025-01-17)


### Features

* Add Big Segment store support ([21c61b8](https://github.com/launchdarkly/php-server-sdk-redis-predis/commit/21c61b831f2aaad61fe4f306a1a81eadc6de20f8))
* Bump LaunchDarkly to 6.4.0+ ([c5c523c](https://github.com/launchdarkly/php-server-sdk-redis-predis/commit/c5c523c672230427748cff9d037a6cec09d22b3f))
* Bump PHP to 8.1+ ([c5c523c](https://github.com/launchdarkly/php-server-sdk-redis-predis/commit/c5c523c672230427748cff9d037a6cec09d22b3f))
* Bump predis to 2.3.0+ ([c5c523c](https://github.com/launchdarkly/php-server-sdk-redis-predis/commit/c5c523c672230427748cff9d037a6cec09d22b3f))
* FeatureRequester requires configured ClientInterface ([ffd1c39](https://github.com/launchdarkly/php-server-sdk-redis-predis/commit/ffd1c39e07dc513db62283a63eecdeadf9527b0d))


### Bug Fixes

* Move DEFAULT_PREFIX const to LaunchDarkly\Integrations ([63c3815](https://github.com/launchdarkly/php-server-sdk-redis-predis/commit/63c3815c51309dbe745e4d0771d95cdaf837a50a))


### Miscellaneous Chores

* Add missing documentation on big segment store method ([295ac15](https://github.com/launchdarkly/php-server-sdk-redis-predis/commit/295ac1507fbe9331e3fec6a0a1192fd15c7eeba9))
* Add psalm and cs-checker ([1794878](https://github.com/launchdarkly/php-server-sdk-redis-predis/commit/179487803e635618efa9dc2fcf76478336c8089f))
* Bump PHP versions in GitHub Actions ([3eedaa5](https://github.com/launchdarkly/php-server-sdk-redis-predis/commit/3eedaa573eacb4ddda0b50b8c57258d432b2a88f))
* Cleanup and strict types ([4155441](https://github.com/launchdarkly/php-server-sdk-redis-predis/commit/41554410e9c63ad0036efb9eaade3bd46c47d467))
* Inline shared test package ([40e93c6](https://github.com/launchdarkly/php-server-sdk-redis-predis/commit/40e93c66b4986c81483f6c93be70a02b03281a4b))
* Pluralize big segments ([fdeae98](https://github.com/launchdarkly/php-server-sdk-redis-predis/commit/fdeae986e149e0265a4ea0c71f070c355183474a))
* Run `composer cs-fix` to improve style ([bc91e22](https://github.com/launchdarkly/php-server-sdk-redis-predis/commit/bc91e225cb8b4876c1feae9202fb084a80165cdc))
* Update type hints to quiet psalm ([839833f](https://github.com/launchdarkly/php-server-sdk-redis-predis/commit/839833f3772bbb5272d6de58573f79fdb6aa2ec6))
* Use real Redis connection for tests ([#30](https://github.com/launchdarkly/php-server-sdk-redis-predis/issues/30)) ([9623a51](https://github.com/launchdarkly/php-server-sdk-redis-predis/commit/9623a5159fdc821561f8f95109eb7908ab1eda1b))

## [1.3.0] - 2023-10-25
### Changed:
- Expanded SDK version support to v6

## [1.2.1] - 2023-01-25
### Fixed:
- Fixed compatibility error with PHP SDK 5.x branch.

## [1.2.0] - 2022-12-23
### Changed:
- The package now allows using Predis 2.x. There are no differences in the parameters unless you are using the `predis_options` parameter to pass custom options directly to Predis, in which case you should use whichever options are valid for the Predis version you are using. ([#12](https://github.com/launchdarkly/php-server-sdk-redis-predis/issues/12))
- Also changed the SDK compatibility constraint to support the upcoming 5.0.0 SDK release.

## [1.1.0] - 2021-10-07
### Added:
- New option `predis_options` allows setting of any options supported by Predis. Previously, the only way to use extended capabilities of Predis was to create your own Predis client instance and pass it in `predis_client`. ([#7](https://github.com/launchdarkly/php-server-sdk-redis-predis/issues/7))

## [1.0.0] - 2021-08-06
Initial release, for use with version 4.x of the LaunchDarkly PHP SDK.

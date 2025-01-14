<?php

declare(strict_types=1);

namespace LaunchDarkly\Impl\Integrations\Tests\Impl\Integrations;

use Exception;
use LaunchDarkly\Impl\Integrations\RedisBigSegmentsStore;
use PHPUnit\Framework;
use Predis\ClientInterface;
use Psr\Log;

class RedisBigSegmentsStoreTest extends Framework\TestCase
{
    public function testGetMetadata(): void
    {
        $now = time();
        $logger = new Log\NullLogger();

        $connection = $this->createMock(ClientInterface::class);
        $store = new RedisBigSegmentsStore($connection, $logger, []);

        $connection->expects($this->once())
            ->method('__call')
            ->with('get', ['launchdarkly:big_segments_synchronized_on'])
            ->willReturn("$now");

        $metadata = $store->getMetadata();

        $this->assertEquals($now, $metadata->getLastUpToDate());
        $this->assertFalse($metadata->isStale(10));
    }

    public function testGetMetadataWithException(): void
    {
        $logger = new Log\NullLogger();

        $connection = $this->createMock(ClientInterface::class);
        $store = new RedisBigSegmentsStore($connection, $logger, []);

        $connection->expects($this->once())
            ->method('__call')
            ->with('get', ['launchdarkly:big_segments_synchronized_on'])
            ->willThrowException(new \Exception('sorry'));

        $metadata = $store->getMetadata();

        $this->assertNull($metadata->getLastUpToDate());
        $this->assertTrue($metadata->isStale(10));
    }

    public function testCanDetectInclusion(): void
    {
        $logger = new Log\NullLogger();

        $connection = $this->createMock(ClientInterface::class);
        $store = new RedisBigSegmentsStore($connection, $logger, []);

        $connection->expects($this->exactly(2))
            ->method('__call')
            ->willReturnCallback(function ($method, $args) {
                if ($method !== 'smembers') {
                    return;
                }

                return match ($args[0]) {
                    'launchdarkly:big_segment_include:ctx' => ['key1', 'key2'],
                    'launchdarkly:big_segment_exclude:ctx' => ['key1', 'key3'],
                    default => [],
                };
            });

        $membership = $store->getMembership('ctx');

        $this->assertCount(3, $membership);
        $this->assertTrue($membership['key1']);
        $this->assertTrue($membership['key2']);
        $this->assertFalse($membership['key3']);
    }

    public function testCanDetectInclusionWithException(): void
    {
        $logger = new Log\NullLogger();

        $connection = $this->createMock(ClientInterface::class);
        $store = new RedisBigSegmentsStore($connection, $logger, []);

        $connection->expects($this->exactly(2))
            ->method('__call')
            ->willReturnCallback(function ($method, $args) {
                if ($method !== 'smembers') {
                    return;
                }

                return match ($args[0]) {
                    'launchdarkly:big_segment_include:ctx' => ['key1', 'key2'],
                    'launchdarkly:big_segment_exclude:ctx' => throw new Exception('sorry'),
                    default => [],
                };
            });

        $membership = $store->getMembership('ctx');

        $this->assertNull($membership);
    }
}

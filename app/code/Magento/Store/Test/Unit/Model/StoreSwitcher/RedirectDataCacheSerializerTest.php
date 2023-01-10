<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model\StoreSwitcher;

use InvalidArgumentException;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreSwitcher\RedirectDataCacheSerializer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

class RedirectDataCacheSerializerTest extends TestCase
{
    private const RANDOM_STRING = '7ddf32e17a6ac5ce04a8ecbf782ca509';
    /**
     * @var CacheInterface|MockObject
     */
    private $cache;
    /**
     * @var RedirectDataCacheSerializer
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = $this->createMock(CacheInterface::class);
        $random = $this->createMock(Random::class);
        $logger = $this->createMock(LoggerInterface::class);
        $this->model = new RedirectDataCacheSerializer(
            new Json(),
            $random,
            $this->cache,
            $logger
        );
        $random->method('getRandomString')->willReturn(self::RANDOM_STRING);
    }

    public function testSerialize(): void
    {
        $this->cache->expects($this->once())
            ->method('save')
            ->with(
                '{"customer_id":1}',
                'store_switch_' . self::RANDOM_STRING,
                [],
                10
            );
        $this->assertEquals(self::RANDOM_STRING, $this->model->serialize(['customer_id' => 1]));
    }

    public function testSerializeShouldThrowExceptionIfCannotSaveCache(): void
    {
        $exception = new RuntimeException('Failed to connect to cache server');
        $this->expectExceptionObject($exception);
        $this->cache->expects($this->once())
            ->method('save')
            ->willThrowException($exception);
        $this->assertEquals(self::RANDOM_STRING, $this->model->serialize(['customer_id' => 1]));
    }

    public function testUnserialize(): void
    {
        $this->cache->expects($this->once())
            ->method('load')
            ->with('store_switch_' . self::RANDOM_STRING)
            ->willReturn('{"customer_id":1}');
        $this->assertEquals(['customer_id' => 1], $this->model->unserialize(self::RANDOM_STRING));
    }

    public function testUnserializeShouldThrowExceptionIfCacheHasExpired(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Couldn\'t retrieve data from cache.'));
        $this->cache->expects($this->once())
            ->method('load')
            ->with('store_switch_' . self::RANDOM_STRING)
            ->willReturn(null);
        $this->assertEquals(['customer_id' => 1], $this->model->unserialize(self::RANDOM_STRING));
    }

    public function testUnserializeShouldThrowExceptionIfCacheKeyIsInvalid(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Invalid cache key \'abc\' supplied.'));
        $this->cache->expects($this->never())
            ->method('load');
        $this->assertEquals(['customer_id' => 1], $this->model->unserialize('abc'));
    }
}

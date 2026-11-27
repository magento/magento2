<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Wishlist\Model\DataSerializer;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DataSerializerTest extends TestCase
{
    public function testUnserializeRemovesTheCacheEntrySavedBySerialize(): void
    {
        $token = str_repeat('a', 32);
        $data = ['product' => 1, 'qty' => 2];
        $savedKey = null;
        $removedKey = null;

        $random = $this->createStub(Random::class);
        $random->method('getRandomString')->willReturn($token);

        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())
            ->method('save')
            ->with($this->anything(), $this->callback(function ($key) use (&$savedKey) {
                $savedKey = $key;
                return true;
            }))
            ->willReturn(true);
        $cache->method('load')->willReturnCallback(
            function ($key) use (&$savedKey, $data) {
                return $key === $savedKey ? json_encode($data) : false;
            }
        );
        $cache->expects($this->once())
            ->method('remove')
            ->willReturnCallback(function ($key) use (&$removedKey) {
                $removedKey = $key;
                return true;
            });

        $model = new DataSerializer(new Json(), $cache, $random, $this->createStub(LoggerInterface::class));

        $this->assertSame($token, $model->serialize($data));
        $this->assertSame($data, $model->unserialize($token));
        $this->assertSame('wishlist_' . $token, $savedKey);
        $this->assertSame($savedKey, $removedKey);
    }
}

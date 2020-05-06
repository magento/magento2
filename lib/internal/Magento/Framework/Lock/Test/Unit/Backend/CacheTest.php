<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Lock\Test\Unit\Backend;

use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Lock\Backend\Cache;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase
{
    const LOCK_PREFIX = 'LOCKED_RECORD_INFO_';

    /**
     * @var FrontendInterface|MockObject
     */
    private $frontendCacheMock;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->frontendCacheMock = $this->getMockForAbstractClass(FrontendInterface::class);

        $objectManager = new ObjectManagerHelper($this);

        $this->cache = $objectManager->getObject(
            Cache::class,
            [
                'cache' => $this->frontendCacheMock
            ]
        );
    }

    /**
     * Verify released a lock.
     *
     * @return void
     */
    public function testUnlock(): void
    {
        $identifier = 'lock_name';

        $closure = \Closure::bind(function ($cacheInstance) {
            return $cacheInstance->lockSign;
        }, null, $this->cache);
        $lockSign = $closure($this->cache);

        $this->frontendCacheMock
            ->expects($this->once())->method('load')
            ->with(self::LOCK_PREFIX . $identifier)
            ->willReturn($lockSign);

        $this->frontendCacheMock
            ->expects($this->once())
            ->method('remove')
            ->with(self::LOCK_PREFIX . $identifier)
            ->willReturn(true);

        $this->assertTrue($this->cache->unlock($identifier));
    }

    /**
     * Verify that lock will no be released without sign matches.
     * Sign generates in Cache class constructor.
     *
     * @return void
     */
    public function testUnlockWithAnotherSign(): void
    {
        $identifier = 'lock_name';

        $this->frontendCacheMock
            ->expects($this->once())->method('load')
            ->with(self::LOCK_PREFIX . $identifier)
            ->willReturn(\uniqid('some_rand-'));

        $this->assertFalse($this->cache->unlock($identifier));
    }
}

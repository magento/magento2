<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Lock\Backend;

use Magento\Framework\Lock\Backend\Cache;

/**
 * \Magento\Framework\Lock\Backend\Cache test case.
 */
class CacheTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Cache
     */
    private $cacheInstance1;

    /**
     * @var Cache
     */
    private $cacheInstance2;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $frontendInterface1 = $objectManager->create(\Magento\Framework\App\Cache\Type\Config::class);
        $this->cacheInstance1 = new Cache($frontendInterface1);

        $frontendInterface2 = $objectManager->create(\Magento\Framework\App\Cache\Type\Config::class);
        $this->cacheInstance2 = new Cache($frontendInterface2);
    }

    /**
     *  Verify lock mechanism in general.
     *
     * @return void
     */
    public function testParallelLock(): void
    {
        $identifier1 = \uniqid('lock_name_1_', true);

        $this->assertTrue($this->cacheInstance1->lock($identifier1, 2));

        $this->assertFalse($this->cacheInstance1->lock($identifier1, 2));
        $this->assertFalse($this->cacheInstance2->lock($identifier1, 2));
        sleep(4);
        $this->assertFalse($this->cacheInstance1->isLocked($identifier1));

        $this->assertTrue($this->cacheInstance2->lock($identifier1, -1));
        sleep(4);
        $this->assertTrue($this->cacheInstance1->isLocked($identifier1));
    }

    /**
     *  Verify that lock will be released after timeout expiration.
     *
     * @return void
     */
    public function testParallelLockExpired(): void
    {
        $identifier1 = \uniqid('lock_name_1_', true);

        $this->assertTrue($this->cacheInstance1->lock($identifier1, 1));
        sleep(2);
        $this->assertFalse($this->cacheInstance1->isLocked($identifier1));

        $this->assertTrue($this->cacheInstance1->lock($identifier1, 1));
        sleep(2);
        $this->assertFalse($this->cacheInstance1->isLocked($identifier1));

        $this->assertTrue($this->cacheInstance2->lock($identifier1, 1));
        sleep(2);
        $this->assertFalse($this->cacheInstance1->isLocked($identifier1));
    }

    /**
     * Verify that lock will not be released by another lock name.
     *
     * @return void
     */
    public function testParallelUnlock(): void
    {
        $identifier1 = \uniqid('lock_name_1_', true);
        $identifier2 = \uniqid('lock_name_2_', true);

        $this->assertTrue($this->cacheInstance1->lock($identifier1, 30));
        $this->assertTrue($this->cacheInstance2->lock($identifier2, 30));

        $this->assertFalse($this->cacheInstance2->unlock($identifier1));
        $this->assertTrue($this->cacheInstance2->unlock($identifier2));

        $this->assertTrue($this->cacheInstance2->isLocked($identifier1));
        $this->assertFalse($this->cacheInstance2->isLocked($identifier2));
    }

    /**
     *  Verify that lock will not be released by another lock name when both locks will never be expired.
     *
     * @return void
     */
    public function testParallelUnlockNoExpiration(): void
    {
        $identifier1 = \uniqid('lock_name_1_', true);
        $identifier2 = \uniqid('lock_name_2_', true);

        $this->assertTrue($this->cacheInstance1->lock($identifier1, -1));
        $this->assertTrue($this->cacheInstance2->lock($identifier2, -1));

        $this->assertFalse($this->cacheInstance2->unlock($identifier1));
        $this->assertTrue($this->cacheInstance2->unlock($identifier2));

        $this->assertTrue($this->cacheInstance2->isLocked($identifier1));
        $this->assertFalse($this->cacheInstance2->isLocked($identifier2));
    }
}

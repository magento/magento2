<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Cache\Test\Unit;

use Magento\Framework\Cache\CompositeStaleCacheNotifier;
use Magento\Framework\Cache\StaleCacheNotifierInterface;
use PHPUnit\Framework\TestCase;

/** Test case for composite cache notifier */
class CompositeStaleCacheNotifierTest extends TestCase implements StaleCacheNotifierInterface
{
    /** @var string[] */
    private $notifications = [];

    /** @test */
    public function testNoNotifications()
    {
        new CompositeStaleCacheNotifier([$this, $this, $this]);

        $this->assertEquals([], $this->notifications);
    }
    
    /** @test */
    public function testNotifiesAllRegisteredNotifiersOfStaleContent()
    {
        $notifier = new CompositeStaleCacheNotifier([$this, $this]);
        $notifier->cacheLoaderIsUsingStaleCache();

        $this->assertEquals(['staleCacheLoaded', 'staleCacheLoaded'], $this->notifications);
    }

    /**
     * Self-shunting notifier to test behavior of composite
     */
    public function cacheLoaderIsUsingStaleCache(): void
    {
        $this->notifications[] = 'staleCacheLoaded';
    }
}

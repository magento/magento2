<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
    public function noNotifications()
    {
        new CompositeStaleCacheNotifier([$this, $this, $this]);

        $this->assertEquals([], $this->notifications);
    }
    
    /** @test */
    public function notifiesAllRegisteredNotifiersOfStaleContent()
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

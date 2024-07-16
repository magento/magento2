<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model\Indexer;

use Magento\Framework\Indexer\CacheContext;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test deferring cache clean up
 */
class DeferCacheCleaningTest extends TestCase
{
    /**
     * Test that cache tags registrations are deferred if cache context is deferred
     */
    public function testDeferredCacheCleaning(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var DeferredCacheContext $deferredCacheContext */
        $deferredCacheContext = $objectManager->get(DeferredCacheContext::class);
        /** @var CacheContext $cacheContext */
        $cacheContext = $objectManager->get(CacheContext::class);
        $cacheContext->flush();
        $deferredCacheContext->start();
        $cacheContext->registerTags(['test_tag_1', 'test_tag_2']);
        $cacheContext->registerEntities('test_tag_ent_1', [1, 2, 3]);
        $deferredCacheContext->start();
        $cacheContext->registerEntities('test_tag_ent_2', [7, 8, 9]);
        $cacheContext->registerTags(['test_tag_1', 'test_tag_3']);
        $this->assertEmpty($cacheContext->getIdentities());
        $deferredCacheContext->commit();
        $this->assertEmpty($cacheContext->getIdentities());
        $deferredCacheContext->commit();
        $this->assertEquals(
            [
                'test_tag_ent_1_1',
                'test_tag_ent_1_2',
                'test_tag_ent_1_3',
                'test_tag_ent_2_7',
                'test_tag_ent_2_8',
                'test_tag_ent_2_9',
                'test_tag_1',
                'test_tag_2',
                'test_tag_3'
            ],
            $cacheContext->getIdentities()
        );
        $cacheContext->flush();
    }
}

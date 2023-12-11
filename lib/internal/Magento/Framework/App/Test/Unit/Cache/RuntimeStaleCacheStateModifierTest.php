<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Cache;

use Magento\Framework\App\Cache\InMemoryState;
use Magento\Framework\App\Cache\RuntimeStaleCacheStateModifier;
use PHPUnit\Framework\TestCase;

/**
 * Test case for runtime state modifier
 */
class RuntimeStaleCacheStateModifierTest extends TestCase
{
    /** @var InMemoryState */
    private $cacheState;

    protected function setUp(): void
    {
        $this->cacheState = new InMemoryState(
            [
                'cache_one' => true,
                'cache_two' => true,
                'cache_three' => true,
                'cache_four' => false
            ]
        );
    }

    /** @test */
    public function doesNotModifyStateWithoutNotification()
    {
        new RuntimeStaleCacheStateModifier($this->cacheState, ['cache_one', 'cache_three']);

        $this->assertEquals(
            new InMemoryState(
                [
                    'cache_one' => true,
                    'cache_two' => true,
                    'cache_three' => true,
                    'cache_four' => false
                ]
            ),
            $this->cacheState
        );
    }

    /** @test */
    public function modifiesOnlyConfiguredCacheTypesOnNotifiedStaleCache()
    {
        $stateModifier = new RuntimeStaleCacheStateModifier($this->cacheState, ['cache_one', 'cache_three']);

        $stateModifier->cacheLoaderIsUsingStaleCache();

        $this->assertEquals(
            [
                false,
                true,
                false
            ],
            [
                $this->cacheState->isEnabled('cache_one'),
                $this->cacheState->isEnabled('cache_two'),
                $this->cacheState->isEnabled('cache_three')
            ]
        );
    }

    /** @test */
    public function doesNotPersistModifiedCacheTypes()
    {
        $stateModifier = new RuntimeStaleCacheStateModifier($this->cacheState, ['cache_one', 'cache_three']);

        $stateModifier->cacheLoaderIsUsingStaleCache();

        $this->assertEquals(
            new InMemoryState(
                [
                    'cache_one' => true,
                    'cache_two' => true,
                    'cache_three' => true,
                    'cache_four' => false
                ]
            ),
            $this->cacheState->withPersistedState([])
        );
    }
}

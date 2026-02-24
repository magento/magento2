<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\App\Test\Unit\Cache;

use Magento\Framework\App\Cache\InMemoryState;
use Magento\Framework\App\Cache\RuntimeStaleCacheStateModifier;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

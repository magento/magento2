<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Cache;

use Magento\Framework\App\Cache\InMemoryState;
use PHPUnit\Framework\TestCase;

/**
 * InMemory Cache State manager
 */
class InMemoryStateTest extends TestCase
{
    /** @var InMemoryState */
    private $state;

    protected function setUp(): void
    {
        $this->state = new InMemoryState();
    }

    /** @test */
    public function allCachesAreDisabledByDefault()
    {
        $this->assertSame(
            [false, false],
            [$this->state->isEnabled('cache_type_one'), $this->state->isEnabled('cache_type_two')]
        );
    }

    /** @test */
    public function enablesOnlySpecificCacheType()
    {
        $this->state->setEnabled('cache_type_two', true);

        $this->assertSame(
            [
                false,
                true,
                false
            ],
            [
                $this->state->isEnabled('cache_type_one'),
                $this->state->isEnabled('cache_type_two'),
                $this->state->isEnabled('cache_type_three')
            ]
        );
    }

    /** @test */
    public function allowsToSpecifyCacheTypeConfiguration()
    {
        $state = $this->state->withPersistedState(
            [
                'cache_type_one' => true,
                'cache_type_three' => true
            ]
        );

        $this->assertSame(
            [
                true,
                false,
                true
            ],
            [
                $state->isEnabled('cache_type_one'),
                $state->isEnabled('cache_type_two'),
                $state->isEnabled('cache_type_three')
            ]
        );
    }

    /** @test */
    public function mergesPersistentStateInTheFinalObject()
    {
        $state = $this->state
            ->withPersistedState(
                [
                    'key2' => true,
                    'key3' => false
                ]
            )
            ->withPersistedState(
                [
                    'key1' => false,
                    'key4' => true,
                ]
            );

        $this->assertEquals(
            new InMemoryState(
                [
                    'key1' => false,
                    'key2' => true,
                    'key3' => false,
                    'key4' => true
                ]
            ),
            $state
        );
    }

    /** @test */
    public function runtimeValuesAreAreNotPreservedWhenPersistedStateIsModified()
    {
        $state = $this->state
            ->withPersistedState(
                [
                    'key1' => true,
                    'key2' => false
                ]
            );

        $state->setEnabled('key1', false);

        $this->assertEquals(
            new InMemoryState(
                [
                    'key1' => true,
                    'key2' => false
                ]
            ),
            $state->withPersistedState([])
        );
    }

    /** @test */
    public function persistingStoresRuntimeValuesPersistedState()
    {
        $state = $this->state
            ->withPersistedState(
                [
                    'key1' => true,
                    'key2' => false,
                    'key3' => false
                ]
            );

        $state->setEnabled('key2', true);
        $state->persist();

        $this->assertEquals(
            new InMemoryState(
                [
                    'key1' => true,
                    'key2' => true,
                    'key3' => false
                ]
            ),
            $state
        );
    }
}

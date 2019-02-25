<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PageCache\Observer\SwitchPageCacheOnMaintenance;

use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Page Cache state test.
 */
class PageCacheStateTest extends TestCase
{
    /**
     * @var PageCacheState
     */
    private $pageCacheStateStorage;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->pageCacheStateStorage = $objectManager->get(PageCacheState::class);
    }

    /**
     * Tests save state.
     *
     * @param bool $state
     * @return void
     * @dataProvider saveStateProvider
     */
    public function testSave(bool $state)
    {
        $this->pageCacheStateStorage->save($state);
        $this->assertEquals($state, $this->pageCacheStateStorage->isEnabled());
    }

    /**
     * Tests flush state.
     *
     * @return void
     */
    public function testFlush()
    {
        $this->pageCacheStateStorage->save(true);
        $this->assertTrue($this->pageCacheStateStorage->isEnabled());
        $this->pageCacheStateStorage->flush();
        $this->assertFalse($this->pageCacheStateStorage->isEnabled());
    }

    /**
     * Save state provider.
     *
     * @return array
     */
    public function saveStateProvider(): array
    {
        return [[true], [false]];
    }
}

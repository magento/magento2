<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Observer;

use PHPUnit\Framework\TestCase;
use Magento\PageCache\Observer\SwitchPageCacheOnMaintenance;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\Event\Observer;
use Magento\PageCache\Model\Cache\Type as PageCacheType;
use Magento\PageCache\Observer\SwitchPageCacheOnMaintenance\PageCacheState;

/**
 * SwitchPageCacheOnMaintenance observer test.
 */
class SwitchPageCacheOnMaintenanceTest extends TestCase
{
    /**
     * @var SwitchPageCacheOnMaintenance
     */
    private $model;

    /**
     * @var Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cacheManager;

    /**
     * @var PageCacheState|\PHPUnit\Framework\MockObject\MockObject
     */
    private $pageCacheStateStorage;

    /**
     * @var Observer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $observer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->cacheManager = $this->createMock(Manager::class);
        $this->pageCacheStateStorage = $this->createMock(PageCacheState::class);
        $this->observer = $this->createMock(Observer::class);

        $this->model = $objectManager->getObject(SwitchPageCacheOnMaintenance::class, [
            'cacheManager' => $this->cacheManager,
            'pageCacheStateStorage' => $this->pageCacheStateStorage,
        ]);
    }

    /**
     * Tests execute when setting maintenance mode to on.
     *
     * @param array $cacheStatus
     * @param bool $cacheState
     * @param int $flushCacheCalls
     * @return void
     * @dataProvider enablingPageCacheStateProvider
     */
    public function testExecuteWhileMaintenanceEnabling(
        array $cacheStatus,
        bool $cacheState,
        int $flushCacheCalls
    ): void {
        $this->observer->method('getData')
            ->with('isOn')
            ->willReturn(true);
        $this->cacheManager->method('getStatus')
            ->willReturn($cacheStatus);

        // Page Cache state will be stored.
        $this->pageCacheStateStorage->expects($this->once())
            ->method('save')
            ->with($cacheState);

        // Page Cache will be cleaned and disabled
        $this->cacheManager->expects($this->exactly($flushCacheCalls))
            ->method('clean')
            ->with([PageCacheType::TYPE_IDENTIFIER]);
        $this->cacheManager->expects($this->exactly($flushCacheCalls))
            ->method('setEnabled')
            ->with([PageCacheType::TYPE_IDENTIFIER], false);

        $this->model->execute($this->observer);
    }

    /**
     * Tests execute when setting Maintenance Mode to off.
     *
     * @param bool $storedCacheState
     * @param int $enableCacheCalls
     * @return void
     * @dataProvider disablingPageCacheStateProvider
     */
    public function testExecuteWhileMaintenanceDisabling(bool $storedCacheState, int $enableCacheCalls): void
    {
        $this->observer->method('getData')
            ->with('isOn')
            ->willReturn(false);

        $this->pageCacheStateStorage->method('isEnabled')
            ->willReturn($storedCacheState);

        // Nullify Page Cache state.
        $this->pageCacheStateStorage->expects($this->once())
            ->method('flush');

        // Page Cache will be enabled.
        $this->cacheManager->expects($this->exactly($enableCacheCalls))
            ->method('setEnabled')
            ->with([PageCacheType::TYPE_IDENTIFIER]);

        $this->model->execute($this->observer);
    }

    /**
     * Page Cache state data provider.
     *
     * @return array
     */
    public function enablingPageCacheStateProvider(): array
    {
        return [
            'page_cache_is_enable' => [
                'cache_status' => [PageCacheType::TYPE_IDENTIFIER => 1],
                'cache_state' => true,
                'flush_cache_calls' => 1,
            ],
            'page_cache_is_missing_in_system' => [
                'cache_status' => [],
                'cache_state' => false,
                'flush_cache_calls' => 0,
            ],
            'page_cache_is_disable' => [
                'cache_status' => [PageCacheType::TYPE_IDENTIFIER => 0],
                'cache_state' => false,
                'flush_cache_calls' => 0,
            ],
        ];
    }

    /**
     * Page Cache state data provider.
     *
     * @return array
     */
    public function disablingPageCacheStateProvider(): array
    {
        return [
            ['stored_cache_state' => true, 'enable_cache_calls' => 1],
            ['stored_cache_state' => false, 'enable_cache_calls' => 0],
        ];
    }
}

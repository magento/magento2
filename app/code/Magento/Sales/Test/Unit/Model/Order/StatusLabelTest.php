<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\RendererInterface;
use Magento\Sales\Model\Order\Status;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\Order\StatusLabel;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests request-scoped order status label caching.
 */
class StatusLabelTest extends TestCase
{
    /**
     * @param string $area
     * @param string|null $label
     * @return void
     */
    #[DataProvider('labelProvider')]
    public function testRepeatedReadsLoadOnce(string $area, ?string $label): void
    {
        $status = $this->createPartialMock(Status::class, ['load']);
        $status->setData('label', $label);
        $status->expects($this->once())->method('load')->with('complete')->willReturnSelf();
        $status->setData('store_labels', [1 => $label]);
        $factory = $this->createMock(StatusFactory::class);
        $factory->expects($this->once())->method('create')->willReturn($status);
        $model = new StatusLabel($factory, $this->createMock(State::class), [], $this->storeManager());

        self::assertSame($label, $model->getStatusFrontendLabel('complete', $area, 1));
        self::assertSame($label, $model->getStatusFrontendLabel('complete', $area, 1));
    }

    /**
     * @return array
     */
    public static function labelProvider(): array
    {
        return [
            'store label' => [Area::AREA_FRONTEND, 'Complete'],
            'empty label' => [Area::AREA_FRONTEND, ''],
            'admin label' => [Area::AREA_ADMINHTML, 'Complete'],
            'missing admin label' => [Area::AREA_ADMINHTML, null],
        ];
    }

    /**
     * @return void
     */
    public function testStatusesAndAreasAreIsolatedAfterMasking(): void
    {
        $complete = $this->createPartialMock(Status::class, ['load']);
        $complete->setData('label', 'Admin complete');
        $complete->setData('store_labels', [1 => 'Store complete']);
        $pending = $this->createPartialMock(Status::class, ['load']);
        $pending->setData('store_labels', [1 => 'Store pending']);
        $loader = $this->createPartialMock(Status::class, ['load']);
        $loader->expects($this->exactly(3))->method('load')->willReturnMap([
            ['complete', null, $complete],
            ['pending', null, $pending],
        ]);
        $factory = $this->createMock(StatusFactory::class);
        $factory->expects($this->exactly(3))->method('create')->willReturn($loader);
        $model = new StatusLabel(
            $factory,
            $this->createMock(State::class),
            [Area::AREA_FRONTEND => ['processing' => 'complete']],
            $this->storeManager()
        );

        for ($read = 0; $read < 2; $read++) {
            self::assertSame('Store complete', $model->getStatusFrontendLabel('processing', Area::AREA_FRONTEND, 1));
            self::assertSame('Store pending', $model->getStatusFrontendLabel('pending', Area::AREA_FRONTEND, 1));
            self::assertSame('Admin complete', $model->getStatusFrontendLabel('complete', Area::AREA_ADMINHTML, 1));
        }
    }

    /**
     * @return void
     */
    public function testCurrentStoreIsResolvedBeforeCaching(): void
    {
        $first = $this->createMock(StoreInterface::class);
        $first->method('getId')->willReturn(1);
        $second = $this->createMock(StoreInterface::class);
        $second->method('getId')->willReturn(2);
        $current = $first;
        $stores = $this->createMock(StoreManagerInterface::class);
        $stores->method('getStore')->willReturnCallback(
            static function ($storeId) use (&$current, $first, $second) {
                return $storeId === null ? $current : [1 => $first, 2 => $second][$storeId];
            }
        );
        $status = $this->createPartialMock(Status::class, ['load']);
        $status->expects($this->exactly(2))->method('load')->with('complete')->willReturnSelf();
        $status->setData('store_labels', [1 => 'First', 2 => 'Second']);
        $factory = $this->createMock(StatusFactory::class);
        $factory->expects($this->exactly(2))->method('create')->willReturn($status);
        $model = new StatusLabel($factory, $this->createMock(State::class), [], $stores);

        self::assertSame('First', $model->getStatusFrontendLabel('complete', Area::AREA_FRONTEND));
        $current = $second;
        self::assertSame('Second', $model->getStatusFrontendLabel('complete', Area::AREA_FRONTEND));
        self::assertSame('First', $model->getStatusFrontendLabel('complete', Area::AREA_FRONTEND, 1));
        self::assertSame('Second', $model->getStatusFrontendLabel('complete', Area::AREA_FRONTEND, 2));
    }

    /**
     * @return void
     */
    public function testResetLoadsFreshLabel(): void
    {
        $status = $this->createPartialMock(Status::class, ['load']);
        $status->setData('label', 'Before');
        $status->expects($this->exactly(2))->method('load')->willReturnSelf();
        $factory = $this->createMock(StatusFactory::class);
        $factory->expects($this->exactly(2))->method('create')->willReturn($status);
        $model = new StatusLabel($factory, $this->createMock(State::class), [], $this->storeManager());

        self::assertSame('Before', $model->getStatusFrontendLabel('complete', Area::AREA_ADMINHTML));
        $status->setData('label', 'After');
        $model->_resetState();
        self::assertSame('After', $model->getStatusFrontendLabel('complete', Area::AREA_ADMINHTML));
    }

    /**
     * @return void
     */
    public function testFallbackTranslationChangesWithoutReloadingStatus(): void
    {
        $status = $this->createPartialMock(Status::class, ['load']);
        $status->setData(['label' => 'Default', 'store_labels' => []]);
        $status->expects($this->once())->method('load')->willReturnSelf();
        $factory = $this->createMock(StatusFactory::class);
        $factory->expects($this->once())->method('create')->willReturn($status);
        $model = new StatusLabel($factory, $this->createMock(State::class), [], $this->storeManager());
        $originalRenderer = Phrase::getRenderer();
        $translation = 'First translation';
        $renderer = $this->createMock(RendererInterface::class);
        $renderer->expects($this->exactly(2))->method('render')->with(['Default'], [])->willReturnCallback(
            static function () use (&$translation): string {
                return $translation;
            }
        );
        Phrase::setRenderer($renderer);
        try {
            self::assertSame(
                'First translation',
                $model->getStatusFrontendLabel('complete', Area::AREA_FRONTEND, 1)
            );
            $translation = 'Second translation';
            self::assertSame(
                'Second translation',
                $model->getStatusFrontendLabel('complete', Area::AREA_FRONTEND, 1)
            );
        } finally {
            Phrase::setRenderer($originalRenderer);
        }
    }

    /**
     * @return StoreManagerInterface
     */
    private function storeManager(): StoreManagerInterface
    {
        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(1);
        $manager = $this->createMock(StoreManagerInterface::class);
        $manager->method('getStore')->willReturn($store);
        return $manager;
    }
}

<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Catalog\Model\ProductOptionProcessorInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderItemSearchResultInterfaceFactory;
use Magento\Sales\Model\Order\ItemRepository;
use Magento\Sales\Model\Order\ProductOption;
use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemRepositoryTest extends TestCase
{
    /**
     * Verify joins precede criteria processing and item loading for both constructor calling conventions.
     *
     * @param bool $injectJoinProcessor
     * @return void
     */
    #[DataProvider('constructorArgumentsProvider')]
    public function testGetList(bool $injectJoinProcessor): void
    {
        $criteria = $this->createStub(SearchCriteriaInterface::class);
        $collection = $this->createMock(Collection::class);
        $factory = $this->createMock(OrderItemSearchResultInterfaceFactory::class);
        $factory->expects($this->once())->method('create')->willReturn($collection);
        $item = $this->createStub(OrderItemInterface::class);
        $events = [];
        $joinProcessor = $this->createMock(JoinProcessorInterface::class);
        $joinProcessor->expects($this->once())->method('process')->with($collection)
            ->willReturnCallback(static function () use (&$events): void {
                $events[] = 'join';
            });
        $collectionProcessor = $this->createMock(CollectionProcessorInterface::class);
        $collectionProcessor->expects($this->once())->method('process')->with($criteria, $collection)
            ->willReturnCallback(static function () use (&$events): void {
                $events[] = 'criteria';
            });
        $collection->expects($this->once())->method('setSearchCriteria')->with($criteria)->willReturnSelf();
        $collection->expects($this->once())->method('getItems')
            ->willReturnCallback(static function () use (&$events, $item): array {
                $events[] = 'items';
                return [$item];
            });
        $productOption = $this->createMock(ProductOption::class);
        $productOption->expects($this->once())->method('add')->with($item)
            ->willReturnCallback(static function () use (&$events): void {
                $events[] = 'options';
            });
        $arguments = [
            $this->createStub(DataObjectFactory::class),
            $this->createStub(Metadata::class),
            $factory,
            $collectionProcessor,
            $productOption,
            ['simple' => $this->createStub(ProductOptionProcessorInterface::class)],
        ];
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->expects($injectJoinProcessor ? $this->never() : $this->once())
            ->method('get')->with(JoinProcessorInterface::class)->willReturn($joinProcessor);
        if ($injectJoinProcessor) {
            $arguments[] = $joinProcessor;
        }
        $instance = new ReflectionProperty(ObjectManager::class, '_instance');
        $previousInstance = $instance->getValue();
        ObjectManager::setInstance($objectManager);
        try {
            $repository = new ItemRepository(...$arguments);
            $this->assertSame($collection, $repository->getList($criteria));
            $this->assertSame(['join', 'criteria', 'items', 'options'], $events);
        } finally {
            $instance->setValue(null, $previousInstance);
        }
    }

    /**
     * @return array
     */
    public static function constructorArgumentsProvider(): array
    {
        return [
            'explicit join processor after nonempty processor pool' => [true],
            'legacy six arguments with nonempty processor pool' => [false],
        ];
    }
}

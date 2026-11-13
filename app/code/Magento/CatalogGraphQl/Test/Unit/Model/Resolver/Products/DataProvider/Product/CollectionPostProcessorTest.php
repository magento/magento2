<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Resolver\Products\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionPostProcessor;
use Magento\Framework\Event\ManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CollectionPostProcessorTest extends TestCase
{
    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManager;

    /**
     * @var Collection|MockObject
     */
    private $collection;

    /**
     * @var CollectionPostProcessor
     */
    private $postProcessor;

    protected function setUp(): void
    {
        $this->eventManager = $this->createMock(ManagerInterface::class);
        $this->collection = $this->createMock(Collection::class);
        $this->collection->expects($this->atLeastOnce())->method('isLoaded')->willReturn(true);
        $this->postProcessor = new CollectionPostProcessor($this->eventManager);
    }

    #[DataProvider('priceFieldsDataProvider')]
    public function testPricesArePreparedForTheWholeCollectionWhenPriceIsRequested(array $attributeNames): void
    {
        $this->collection->expects($this->once())->method('count')->willReturn(2);
        $this->collection->expects($this->once())->method('getStoreId')->willReturn(3);
        $this->collection->expects($this->once())->method('addTierPriceData')->willReturnSelf();
        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with(
                'prepare_catalog_product_collection_prices',
                ['collection' => $this->collection, 'store_id' => 3]
            );

        $this->assertSame($this->collection, $this->postProcessor->process($this->collection, $attributeNames));
    }

    /**
     * @return array
     */
    public static function priceFieldsDataProvider(): array
    {
        return [
            'price_range' => [['sku', 'price_range']],
            'price_tiers' => [['price_tiers']],
            'legacy price' => [['name', 'price']],
        ];
    }

    public function testPricesAreNotPreparedWithoutPriceFields(): void
    {
        $this->collection->expects($this->never())->method('addTierPriceData');
        $this->eventManager->expects($this->never())->method('dispatch');

        $this->postProcessor->process($this->collection, ['sku', 'name']);
    }

    public function testPricesAreNotPreparedForEmptyCollection(): void
    {
        $this->collection->expects($this->once())->method('count')->willReturn(0);
        $this->collection->expects($this->never())->method('addTierPriceData');
        $this->eventManager->expects($this->never())->method('dispatch');

        $this->postProcessor->process($this->collection, ['price_range']);
    }
}

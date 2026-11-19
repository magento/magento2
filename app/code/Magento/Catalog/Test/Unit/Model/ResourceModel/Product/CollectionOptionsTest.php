<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

use ArrayIterator;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Option\Collection as OptionCollection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

class CollectionOptionsTest extends TestCase
{
    public function testAddOptionsToResultUsesCollectionStore(): void
    {
        $currentStore = $this->createMock(Store::class);
        $currentStore->method('getId')->willReturn(1);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($currentStore);

        $resource = $this->createMock(ProductResource::class);
        $resource->method('getLinkField')->willReturn('entity_id');
        $product = $this->createMock(Product::class);
        $product->method('getResource')->willReturn($resource);
        $product->method('getData')->willReturnMap([['entity_id', null, 10]]);

        $option = $this->createPartialMock(Option::class, []);
        $option->setData('product_id', 10);
        $product->expects($this->once())->method('addOption')->with($option)->willReturnSelf();

        $options = $this->createMock(OptionCollection::class);
        $options->expects($this->once())->method('addTitleToResult')->with(2)->willReturnSelf();
        $options->expects($this->once())->method('addPriceToResult')->with(2)->willReturnSelf();
        $options->expects($this->once())->method('addProductToFilter')->with([10])->willReturnSelf();
        $options->expects($this->once())->method('addValuesToResult')->with(2)->willReturnSelf();
        $options->method('getIterator')->willReturn(new ArrayIterator([$option]));

        $optionModel = $this->createMock(Option::class);
        $optionModel->method('getCollection')->willReturn($options);
        $optionFactory = $this->createMock(OptionFactory::class);
        $optionFactory->method('create')->willReturn($optionModel);

        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getIterator'])
            ->getMock();
        $collection->method('getIterator')->willReturn(new ArrayIterator([$product]));
        $objectManager = new ObjectManager($this);
        $objectManager->setBackwardCompatibleProperty($collection, '_storeManager', $storeManager);
        $objectManager->setBackwardCompatibleProperty($collection, '_productOptionFactory', $optionFactory);
        $collection->setStoreId(2);

        self::assertSame($collection, $collection->addOptionsToResult());
    }
}

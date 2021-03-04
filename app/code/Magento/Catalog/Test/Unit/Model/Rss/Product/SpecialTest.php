<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Rss\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class SpecialTest
 * @package Magento\Catalog\Model\Rss\Product
 */
class SpecialTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Rss\Product\Special
     */
    protected $special;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManager;

    protected function setUp(): void
    {
        $this->product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->productFactory = $this->createPartialMock(\Magento\Catalog\Model\ProductFactory::class, ['create']);
        $this->productFactory->expects($this->any())->method('create')->willReturn($this->product);
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManager::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->special = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\Rss\Product\Special::class,
            [
                'productFactory' => $this->productFactory,
                'storeManager' => $this->storeManager
            ]
        );
    }

    public function testGetProductsCollection()
    {
        $storeId = 1;
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->storeManager->expects($this->once())->method('getStore')->with($storeId)->willReturn(
            $store
        );
        $websiteId = 1;
        $store->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection =
            $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $this->product->expects($this->once())->method('getResourceCollection')->willReturn(
            $productCollection
        );
        $customerGroupId = 1;
        $productCollection->expects($this->once())->method('addPriceDataFieldFilter')->willReturnSelf();
        $productCollection->expects($this->once())->method('addPriceData')->with($storeId, $customerGroupId)->willReturnSelf(
            
        );
        $productCollection->expects($this->once())->method('addAttributeToSelect')->willReturnSelf();
        $productCollection->expects($this->once())->method('addAttributeToSort')->willReturnSelf();

        $products = $this->special->getProductsCollection($storeId, $customerGroupId);
        $this->assertEquals($productCollection, $products);
    }
}

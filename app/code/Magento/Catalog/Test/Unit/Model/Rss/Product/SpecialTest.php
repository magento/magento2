<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Rss\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\Rss\Product\Special;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SpecialTest extends TestCase
{
    /**
     * @var Special
     */
    protected $special;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var MockObject
     */
    protected $productFactory;

    /**
     * @var MockObject|Product
     */
    protected $product;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    protected function setUp(): void
    {
        $this->product = $this->createMock(Product::class);
        $this->productFactory = $this->createPartialMock(ProductFactory::class, ['create']);
        $this->productFactory->expects($this->any())->method('create')->willReturn($this->product);
        $this->storeManager = $this->createMock(StoreManager::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->special = $this->objectManagerHelper->getObject(
            Special::class,
            [
                'productFactory' => $this->productFactory,
                'storeManager' => $this->storeManager
            ]
        );
    }

    public function testGetProductsCollection()
    {
        $storeId = 1;
        $store = $this->createMock(Store::class);
        $this->storeManager->expects($this->once())->method('getStore')->with($storeId)->willReturn(
            $store
        );
        $websiteId = 1;
        $store->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);

        /** @var Collection $productCollection */
        $productCollection =
            $this->createMock(Collection::class);
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

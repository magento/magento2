<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Rss\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\Rss\Product\NewProducts;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NewProductsTest extends TestCase
{
    /**
     * @var NewProducts
     */
    protected $newProducts;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var MockObject|ProductFactory
     */
    protected $productFactory;

    /**
     * @var MockObject|Product
     */
    protected $product;

    /**
     * @var Visibility|MockObject
     */
    protected $visibility;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $timezone;

    protected function setUp(): void
    {
        $this->product = $this->createMock(Product::class);
        $this->productFactory = $this->createPartialMock(ProductFactory::class, ['create']);
        $this->productFactory->expects($this->any())->method('create')->will($this->returnValue($this->product));
        $this->visibility = $this->createMock(Visibility::class);
        $this->timezone = $this->createMock(Timezone::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->newProducts = $this->objectManagerHelper->getObject(
            NewProducts::class,
            [
                'productFactory' => $this->productFactory,
                'visibility' => $this->visibility,
                'localeDate' => $this->timezone
            ]
        );
    }

    public function testGetProductsCollection()
    {
        /** @var \DateTime|MockObject $dateObject */
        $dateObject = $this->createMock(\DateTime::class);
        $dateObject->expects($this->any())
            ->method('setTime')
            ->will($this->returnSelf());
        $dateObject->expects($this->any())
            ->method('format')
            ->will($this->returnValue(date(\Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT)));

        $this->timezone->expects($this->exactly(2))
            ->method('date')
            ->will($this->returnValue($dateObject));

        /** @var Collection $productCollection */
        $productCollection =
            $this->createMock(Collection::class);
        $this->product->expects($this->once())->method('getResourceCollection')->will(
            $this->returnValue($productCollection)
        );
        $storeId = 1;
        $productCollection->expects($this->once())->method('setStoreId')->with($storeId);
        $productCollection->expects($this->once())->method('addStoreFilter')->will($this->returnSelf());
        $productCollection->expects($this->any())->method('addAttributeToFilter')->will($this->returnSelf());
        $productCollection->expects($this->any())->method('addAttributeToSelect')->will($this->returnSelf());
        $productCollection->expects($this->once())->method('addAttributeToSort')->will($this->returnSelf());
        $productCollection->expects($this->once())->method('applyFrontendPriceLimitations')->will($this->returnSelf());
        $visibleIds = [1, 3];
        $this->visibility->expects($this->once())->method('getVisibleInCatalogIds')->will(
            $this->returnValue($visibleIds)
        );
        $productCollection->expects($this->once())->method('setVisibility')->with($visibleIds)->will(
            $this->returnSelf()
        );

        $products = $this->newProducts->getProductsCollection($storeId);
        $this->assertEquals($productCollection, $products);
    }
}

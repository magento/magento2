<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\CustomerData;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Catalog\Model\Product\Url;
use Magento\Checkout\CustomerData\DefaultItem;
use Magento\Checkout\Helper\Data;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DefaultItemTest extends TestCase
{
    /**
     * @var DefaultItem
     */
    private $model;

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var ConfigurationPool
     */
    private $configurationPool;

    /**
     * @var ItemResolverInterface|MockObject
     */
    private $itemResolver;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->imageHelper = $this->getMockBuilder(Image::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configurationPool = $this->getMockBuilder(ConfigurationPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $checkoutHelper = $this->getMockBuilder(Data::class)
            ->onlyMethods(['formatPrice'])->disableOriginalConstructor()
            ->getMock();
        $checkoutHelper->expects($this->any())->method('formatPrice')->willReturn(5);
        $this->itemResolver = $this->getMockForAbstractClass(ItemResolverInterface::class);
        $this->model = $objectManager->getObject(
            DefaultItem::class,
            [
                'imageHelper' => $this->imageHelper,
                'configurationPool' => $this->configurationPool,
                'checkoutHelper' => $checkoutHelper,
                'itemResolver' => $this->itemResolver,
            ]
        );
    }

    public function testGetItemData()
    {
        $urlModel = $this->getMockBuilder(Url::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product = $this->getMockBuilder(Product::class)
            ->onlyMethods(['getUrlModel', 'isVisibleInSiteVisibility', 'getSku'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->any())->method('getUrlModel')->willReturn($urlModel);
        $product->expects($this->any())->method('isVisibleInSiteVisibility')->willReturn(true);
        $product->expects($this->any())->method('getSku')->willReturn('simple');
        /** @var Item $item */
        $item = $this->getMockBuilder(Item::class)
            ->onlyMethods(['getProductType', 'getProduct', 'getCalculationPrice'])
            ->disableOriginalConstructor()
            ->getMock();
        $item->expects($this->any())->method('getProduct')->willReturn($product);
        $item->expects($this->any())->method('getProductType')->willReturn('simple');
        $item->expects($this->any())->method('getCalculationPrice')->willReturn(5);

        $this->imageHelper->expects($this->any())->method('init')->with($product)->willReturnSelf();
        $this->imageHelper->expects($this->any())->method('getUrl')->willReturn('url');
        $this->imageHelper->expects($this->any())->method('getLabel')->willReturn('label');
        $this->imageHelper->expects($this->any())->method('getWidth')->willReturn(100);
        $this->imageHelper->expects($this->any())->method('getHeight')->willReturn(100);
        $this->configurationPool->expects($this->any())->method('getByProductType')->willReturn($product);

        $this->itemResolver->expects($this->any())
            ->method('getFinalProduct')
            ->with($item)
            ->willReturn($product);

        $itemData = $this->model->getItemData($item);
        $this->assertArrayHasKey('options', $itemData);
        $this->assertArrayHasKey('qty', $itemData);
        $this->assertArrayHasKey('item_id', $itemData);
        $this->assertArrayHasKey('configure_url', $itemData);
        $this->assertArrayHasKey('is_visible_in_site_visibility', $itemData);
        $this->assertArrayHasKey('product_type', $itemData);
        $this->assertArrayHasKey('product_name', $itemData);
        $this->assertArrayHasKey('product_sku', $itemData);
        $this->assertArrayHasKey('product_url', $itemData);
        $this->assertArrayHasKey('product_has_url', $itemData);
        $this->assertArrayHasKey('product_price', $itemData);
        $this->assertArrayHasKey('product_price_value', $itemData);
        $this->assertArrayHasKey('product_image', $itemData);
        $this->assertArrayHasKey('canApplyMsrp', $itemData);
        $this->assertArrayHasKey('message', $itemData);
    }
}

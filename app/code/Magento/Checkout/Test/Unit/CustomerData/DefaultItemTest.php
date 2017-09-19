<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\CustomerData;

class DefaultItemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Checkout\CustomerData\DefaultItem
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @var \Magento\Catalog\Helper\Product\ConfigurationPool
     */
    private $configurationPool;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->imageHelper = $this->getMockBuilder(\Magento\Catalog\Helper\Image::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configurationPool = $this->getMockBuilder(\Magento\Catalog\Helper\Product\ConfigurationPool::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $checkoutHelper = $this->getMockBuilder(\Magento\Checkout\Helper\Data::class)
            ->setMethods(['formatPrice'])->disableOriginalConstructor()->getMock();
        $checkoutHelper->expects($this->any())->method('formatPrice')->willReturn(5);
        $this->model = $objectManager->getObject(
            \Magento\Checkout\CustomerData\DefaultItem::class,
            [
                'imageHelper' => $this->imageHelper,
                'configurationPool' => $this->configurationPool,
                'checkoutHelper' => $checkoutHelper
            ]
        );
    }

    public function testGetItemData()
    {
        $urlModel = $this->getMockBuilder(\Magento\Catalog\Model\Product\Url::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getUrlModel', 'isVisibleInSiteVisibility', 'getSku'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->any())->method('getUrlModel')->willReturn($urlModel);
        $product->expects($this->any())->method('isVisibleInSiteVisibility')->willReturn(true);
        $product->expects($this->any())->method('getSku')->willReturn('simple');
        /** @var \Magento\Quote\Model\Quote\Item $item */
        $item = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->setMethods(['getProductType', 'getProduct', 'getCalculationPrice'])
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
    }
}

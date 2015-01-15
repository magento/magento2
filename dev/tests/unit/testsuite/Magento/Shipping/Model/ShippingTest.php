<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model;

use Magento\Sales\Model\Quote\Address\RateRequest;
use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ShippingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test identification number of product
     *
     * @var int
     */
    protected $productId = 1;

    /**
     * @var Shipping
     */
    protected $shipping;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Shipping\Model\Carrier\AbstractCarrier
     */
    protected $carrier;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemData;

    protected function setUp()
    {
        $this->carrier = $this->getMock('Magento\Shipping\Model\Carrier\AbstractCarrier', [], [], '', false);
        $this->carrier->expects($this->any())->method('getConfigData')->will($this->returnCallback(function ($key) {
            $configData = [
                'max_package_weight' => 10,
            ];
            return isset($configData[$key]) ? $configData[$key] : 0;
        }));
        $this->stockRegistry = $this->getMock(
            'Magento\CatalogInventory\Model\StockRegistry',
            [],
            [],
            '',
            false
        );
        $this->stockItemData = $this->getMock('Magento\CatalogInventory\Model\Stock\Item', [], [], '', false);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->shipping = $objectManagerHelper->getObject('Magento\Shipping\Model\Shipping', [
            'stockRegistry' => $this->stockRegistry
        ]);
    }

    /**
     * @covers \Magento\Shipping\Model\Shipping::composePackagesForCarrier
     */
    public function testComposePackages()
    {
        $request = new RateRequest();
        /** \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface */
        $item = $this->getMockBuilder('\Magento\Sales\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods([
                'getQty', 'getIsQtyDecimal', 'getProductType', 'getProduct', 'getWeight', '__wakeup', 'getStore',
            ])
            ->getMock();
        $product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);

        $item->expects($this->any())->method('getQty')->will($this->returnValue(1));
        $item->expects($this->any())->method('getWeight')->will($this->returnValue(10));
        $item->expects($this->any())->method('getIsQtyDecimal')->will($this->returnValue(true));
        $item->expects($this->any())->method('getProductType')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE));
        $item->expects($this->any())->method('getProduct')->will($this->returnValue($product));

        $store = $this->getMock('Magento\Store\Model\Store', ['getWebsiteId'], [], '', false);
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnValue(10));
        $item->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $product->expects($this->any())->method('getId')->will($this->returnValue($this->productId));
        $request->setData('all_items', [$item]);

        $this->stockItemData->expects($this->any())->method('getIsDecimalDivided')->will($this->returnValue(true));

        /** Testable service calls to CatalogInventory module */
        $this->stockRegistry->expects($this->atLeastOnce())->method('getStockItem')
            ->with($this->productId, 10)
            ->will($this->returnValue($this->stockItemData));

        $this->stockItemData->expects($this->atLeastOnce())
            ->method('getEnableQtyIncrements')
            ->will($this->returnValue(true));
        $this->stockItemData->expects($this->atLeastOnce())->method('getQtyIncrements')
            ->will($this->returnValue(0.5));

        $this->shipping->composePackagesForCarrier($this->carrier, $request);
    }
}

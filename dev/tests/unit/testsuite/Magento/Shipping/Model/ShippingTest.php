<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Shipping\Model;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\Quote\Address\RateRequest;

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
    protected $stockItemService;

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
        $this->stockItemService = $this->getMock(
            'Magento\CatalogInventory\Service\V1\StockItemService',
            [],
            [],
            '',
            false
        );
        $this->stockItemData = $this->getMock('Magento\CatalogInventory\Service\V1\Data\StockItem', [], [], '', false);
        $this->stockItemService->expects($this->any())->method('getStockItem')
            ->will($this->returnValue($this->stockItemData));

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->shipping = $objectManagerHelper->getObject('Magento\Shipping\Model\Shipping', [
            'stockItemService' => $this->stockItemService
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
            ->setMethods(['getQty', 'getIsQtyDecimal', 'getProductType', 'getProduct', 'getWeight', '__wakeup'])
            ->getMock();
        $product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);

        $item->expects($this->any())->method('getQty')->will($this->returnValue(1));
        $item->expects($this->any())->method('getWeight')->will($this->returnValue(10));
        $item->expects($this->any())->method('getIsQtyDecimal')->will($this->returnValue(true));
        $item->expects($this->any())->method('getProductType')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE));
        $item->expects($this->any())->method('getProduct')->will($this->returnValue($product));
        $product->expects($this->any())->method('getId')->will($this->returnValue($this->productId));
        $request->setData('all_items', [$item]);

        $this->stockItemData->expects($this->any())->method('getIsDecimalDivided')->will($this->returnValue(true));

        /** Testable service calls to CatalogInventory module */
        $this->stockItemService->expects($this->atLeastOnce())->method('getStockItem')->with($this->productId);
        $this->stockItemService->expects($this->atLeastOnce())
            ->method('getEnableQtyIncrements')
            ->with($this->productId)
            ->will($this->returnValue(true));
        $this->stockItemService->expects($this->atLeastOnce())->method('getQtyIncrements')->with($this->productId)
            ->will($this->returnValue(0.5));

        $this->shipping->composePackagesForCarrier($this->carrier, $request);
    }
}

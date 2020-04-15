<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Reorder;

use Magento\Sales\Model\Config;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Reorder\OrderedProductAvailabilityChecker;
use Magento\Sales\Model\Order\Reorder\UnavailableProductsProvider;
use Magento\Sales\Model\Order;

/**
 * Class UnavailableProductsProviderTest
 */
class UnavailableProductsProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $salesConfigMock;

    /**
     * @var OrderedProductAvailabilityChecker|\PHPUnit\Framework\MockObject\MockObject
     */
    private $checkerMock;

    /**
     * @var Order|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderMock;

    /**
     * @var Item|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderItemMock;

    /**
     * @var UnavailableProductsProvider
     */
    private $unavailableProductsProvider;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->salesConfigMock = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $this->checkerMock = $this->getMockBuilder(OrderedProductAvailabilityChecker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $this->orderItemMock = $this->getMockBuilder(Item::class)->disableOriginalConstructor()->getMock();
        $this->unavailableProductsProvider = $objectManager->getObject(
            UnavailableProductsProvider::class,
            [
                'salesConfig' => $this->salesConfigMock,
                'orderedProductAvailabilityChecker' => $this->checkerMock
            ]
        );
    }

    public function testGetForOrder()
    {
        $sku = 'sku';
        $this->isAvailable(false);
        $this->orderItemMock->expects($this->any())->method('getSku')->willReturn($sku);
        $unavailableProducts[] = $sku;

        $this->assertEquals(
            $unavailableProducts,
            $this->unavailableProductsProvider->getForOrder($this->orderMock)
        );
    }

    public function testGetForOrderEmpty()
    {
        $this->isAvailable(true);

        $this->assertEquals([], $this->unavailableProductsProvider->getForOrder($this->orderMock));
    }

    /**
     * @param bool $result
     */
    private function isAvailable($result)
    {
        $productTypes = ['configurable'];
        $this->salesConfigMock->expects($this->any())
            ->method('getAvailableProductTypes')
            ->willReturn($productTypes);
        $this->orderMock->expects($this->any())
            ->method('getItemsCollection')
            ->with($productTypes, false)
            ->willReturn([$this->orderItemMock]);
        $this->checkerMock->expects($this->any())
            ->method('isAvailable')
            ->with($this->orderItemMock)
            ->willReturn($result);
    }
}

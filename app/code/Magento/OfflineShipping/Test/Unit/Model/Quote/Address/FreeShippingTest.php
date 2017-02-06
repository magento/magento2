<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\OfflineShipping\Test\Unit\Model\Quote\Address;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class FreeShippingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\OfflineShipping\Model\Quote\Address\FreeShipping
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\StoreManagerInterface
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\OfflineShipping\Model\SalesRule\Calculator
     */
    private $calculatorMock;

    protected function setUp()
    {
        $this->storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->calculatorMock = $this->getMock(
            \Magento\OfflineShipping\Model\SalesRule\Calculator::class,
            [],
            [],
            '',
            false
        );

        $this->model = new \Magento\OfflineShipping\Model\Quote\Address\FreeShipping(
            $this->storeManagerMock,
            $this->calculatorMock
        );
    }

    public function testIsFreeShippingIfNoItems()
    {
        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $this->assertFalse($this->model->isFreeShipping($quoteMock, []));
    }

    public function testIsFreeShipping()
    {
        $storeId = 100;
        $websiteId = 200;
        $customerGroupId = 300;
        $objectManagerMock = new ObjectManagerHelper($this);
        $quoteMock = $this->getMock(
            \Magento\Quote\Model\Quote::class,
            ['getShippingAddress', 'getStoreId', 'getCustomerGroupId', 'getCouponCode'],
            [],
            '',
            false
        );
        $itemMock = $this->getMock(
            \Magento\Quote\Model\Quote\Item::class,
            [
                'getNoDiscount',
                'getParentItemId',
                'getFreeShipping',
                'getAddress',
                'isChildrenCalculated',
                'getHasChildren',
                'getChildren'
            ],
            [],
            '',
            false
        );

        $quoteMock->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $storeMock = $this->getMock(\Magento\Store\Api\Data\StoreInterface::class);
        $storeMock->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);
        $this->storeManagerMock->expects($this->once())->method('getStore')->with($storeId)->willReturn($storeMock);

        $quoteMock->expects($this->once())->method('getCustomerGroupId')->willReturn($customerGroupId);
        $quoteMock->expects($this->once())->method('getCouponCode')->willReturn(null);

        $this->calculatorMock->expects($this->once())
            ->method('init')
            ->with($websiteId, $customerGroupId, null)
            ->willReturnSelf();

        $itemMock->expects($this->once())->method('getNoDiscount')->willReturn(false);
        $itemMock->expects($this->once())->method('getParentItemId')->willReturn(false);
        $this->calculatorMock->expects($this->exactly(2))->method('processFreeShipping')->willReturnSelf();
        $itemMock->expects($this->once())->method('getFreeShipping')->willReturn(true);

        $addressMock = $objectManagerMock->getObject(\Magento\Quote\Model\Quote\Address::class);
        $quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($addressMock);
        $itemMock->expects($this->exactly(2))->method('getAddress')->willReturn($addressMock);

        $itemMock->expects($this->once())->method('getHasChildren')->willReturn(true);
        $itemMock->expects($this->once())->method('isChildrenCalculated')->willReturn(true);

        $childMock = $this->getMock(\Magento\Quote\Model\Quote\Item::class, ['setFreeShipping'], [], '', false);
        $childMock->expects($this->once())->method('setFreeShipping')->with(true)->willReturnSelf();
        $itemMock->expects($this->once())->method('getChildren')->willReturn([$childMock]);

        $this->assertTrue($this->model->isFreeShipping($quoteMock, [$itemMock]));
    }
}

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Observer\Frontend;

use \Magento\Sales\Observer\Frontend\AddVatRequestParamsOrderComment;

/**
 * Tests Magento\Sales\Observer\Frontend\AddVatRequestParamsOrderComment
 */
class AddVatRequestParamsOrderCommentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Helper\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAddressHelperMock;

    /**
     * @var AddVatRequestParamsOrderComment
     */
    protected $observer;

    protected function setUp()
    {
        $this->customerAddressHelperMock = $this->getMockBuilder(\Magento\Customer\Helper\Address::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = new AddVatRequestParamsOrderComment(
            $this->customerAddressHelperMock
        );
    }

    /**
     * @param string $configAddressType
     * @param string|int $vatRequestId
     * @param string|int $vatRequestDate
     * @param string $orderHistoryComment
     * @dataProvider addVatRequestParamsOrderCommentDataProvider
     */
    public function testAddVatRequestParamsOrderComment(
        $configAddressType,
        $vatRequestId,
        $vatRequestDate,
        $orderHistoryComment
    ) {
        $this->customerAddressHelperMock->expects($this->once())
            ->method('getTaxCalculationAddressType')
            ->will($this->returnValue($configAddressType));

        $orderAddressMock = $this->getMock(
            \Magento\Sales\Model\Order\Address::class,
            ['getVatRequestId', 'getVatRequestDate', '__wakeup'],
            [],
            '',
            false
        );
        $orderAddressMock->expects($this->any())
            ->method('getVatRequestId')
            ->will($this->returnValue($vatRequestId));
        $orderAddressMock->expects($this->any())
            ->method('getVatRequestDate')
            ->will($this->returnValue($vatRequestDate));

        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShippingAddress', '__wakeup', 'addStatusHistoryComment', 'getBillingAddress'])
            ->getMock();
        $orderMock->expects($this->any())
            ->method('getShippingAddress')
            ->will($this->returnValue($orderAddressMock));
        if ($orderHistoryComment === null) {
            $orderMock->expects($this->never())
                ->method('addStatusHistoryComment');
        } else {
            $orderMock->expects($this->once())
                ->method('addStatusHistoryComment')
                ->with($orderHistoryComment, false);
        }
        $observer = $this->getMock(\Magento\Framework\Event\Observer::class, ['getOrder'], [], '', false);
        $observer->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue($orderMock));

        $this->assertNull($this->observer->execute($observer));
    }

    public function addVatRequestParamsOrderCommentDataProvider()
    {
        return [
            [
                \Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING,
                'vatRequestId',
                'vatRequestDate',
                'VAT Request Identifier: vatRequestId<br />VAT Request Date: vatRequestDate',
            ],
            [
                \Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING,
                1,
                'vatRequestDate',
                null,
            ],
            [
                \Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING,
                'vatRequestId',
                1,
                null,
            ],
            [
                null,
                'vatRequestId',
                'vatRequestDate',
                null,
            ],
            [
                \Magento\Customer\Model\Address\AbstractAddress::TYPE_BILLING,
                'vatRequestId',
                'vatRequestDate',
                null,
            ],
        ];
    }
}

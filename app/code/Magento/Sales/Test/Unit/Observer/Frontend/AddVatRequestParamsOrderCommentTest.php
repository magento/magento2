<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Observer\Frontend;

use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use Magento\Sales\Observer\Frontend\AddVatRequestParamsOrderComment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests Magento\Sales\Observer\Frontend\AddVatRequestParamsOrderComment
 */
class AddVatRequestParamsOrderCommentTest extends TestCase
{
    /**
     * @var Address|MockObject
     */
    protected $customerAddressHelperMock;

    /**
     * @var AddVatRequestParamsOrderComment
     */
    protected $observer;

    protected function setUp(): void
    {
        $this->customerAddressHelperMock = $this->getMockBuilder(Address::class)
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
            ->willReturn($configAddressType);

        $orderAddressMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Address::class,
            ['getVatRequestId', 'getVatRequestDate']
        );
        $orderAddressMock->expects($this->any())
            ->method('getVatRequestId')
            ->willReturn($vatRequestId);
        $orderAddressMock->expects($this->any())
            ->method('getVatRequestDate')
            ->willReturn($vatRequestDate);

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShippingAddress', 'addStatusHistoryComment', 'getBillingAddress'])
            ->getMock();
        $orderMock->expects($this->any())
            ->method('getShippingAddress')
            ->willReturn($orderAddressMock);
        if ($orderHistoryComment === null) {
            $orderMock->expects($this->never())
                ->method('addStatusHistoryComment');
        } else {
            $orderMock->expects($this->once())
                ->method('addStatusHistoryComment')
                ->with($orderHistoryComment, false);
        }
        $observer = $this->getMockBuilder(Observer::class)
            ->addMethods(['getOrder'])
            ->disableOriginalConstructor()
            ->getMock();
        $observer->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock);

        $this->assertNull($this->observer->execute($observer));
    }

    /**
     * @return array
     */
    public function addVatRequestParamsOrderCommentDataProvider()
    {
        return [
            [
                AbstractAddress::TYPE_SHIPPING,
                'vatRequestId',
                'vatRequestDate',
                'VAT Request Identifier: vatRequestId<br />VAT Request Date: vatRequestDate',
            ],
            [
                AbstractAddress::TYPE_SHIPPING,
                1,
                'vatRequestDate',
                null,
            ],
            [
                AbstractAddress::TYPE_SHIPPING,
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
                AbstractAddress::TYPE_BILLING,
                'vatRequestId',
                'vatRequestDate',
                null,
            ],
        ];
    }
}

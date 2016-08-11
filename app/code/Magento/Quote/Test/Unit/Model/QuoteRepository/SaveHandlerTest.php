<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\QuoteRepository;

use Magento\Quote\Model\QuoteRepository\SaveHandler;

class SaveHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cartItemPersister;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $billingAddressPersister;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteResourceModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingAssignmentPersister;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $itemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $billingAddressMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingAssignmentMock;

    protected function setUp()
    {
        $this->quoteResourceModel = $this->getMock(\Magento\Quote\Model\ResourceModel\Quote::class, [], [], '', false);
        $this->cartItemPersister = $this->getMock(
            \Magento\Quote\Model\Quote\Item\CartItemPersister::class,
            [],
            [],
            '',
            false
        );
        $this->billingAddressPersister  = $this->getMock(
            \Magento\Quote\Model\Quote\Address\BillingAddressPersister::class,
            [],
            [],
            '',
            false
        );
        $this->shippingAssignmentPersister = $this->getMock(
            \Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentPersister::class,
            [],
            [],
            '',
            false
        );
        $methods = [
            'getItems', 'setLastAddedItem', 'getBillingAddress', 'getIsActive',
            'getExtensionAttributes', 'isVirtual', 'collectTotals'
        ];
        $this->quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, $methods, [], '', false);
        $this->itemMock = $this->getMock(\Magento\Quote\Model\Quote\Item::class, [], [], '', false);
        $this->billingAddressMock = $this->getMock(\Magento\Quote\Model\Quote\Address::class, [], [], '', false);
        $this->extensionAttributeMock = $this->getMock(\Magento\Quote\Api\Data\CartExtensionInterface::class);
        $this->shippingAssignmentMock =
            $this->getMock(
                \Magento\Quote\Api\Data\CartExtension::class,
                ['getShippingAssignments', 'setShippingAssignments'],
                [],
                '',
                false
            );
        $this->saveHandler = new SaveHandler(
            $this->quoteResourceModel,
            $this->cartItemPersister,
            $this->billingAddressPersister,
            $this->shippingAssignmentPersister
        );

    }

    public function testSaveForVirtualQuote()
    {
        $this->quoteMock->expects($this->once())->method('getItems')->willReturn([$this->itemMock]);
        $this->itemMock->expects($this->once())->method('isDeleted')->willReturn(false);
        $this->cartItemPersister
            ->expects($this->once())
            ->method('save')
            ->with($this->quoteMock, $this->itemMock)
            ->willReturn($this->itemMock);
        $this->quoteMock->expects($this->once())->method('setLastAddedItem')->with($this->itemMock);
        $this->quoteMock->expects($this->once())->method('getBillingAddress')->willReturn($this->billingAddressMock);
        $this->billingAddressPersister
            ->expects($this->once())
            ->method('save')
            ->with($this->quoteMock, $this->billingAddressMock);
        $this->quoteMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributeMock);
        $this->extensionAttributeMock
            ->expects($this->never())
            ->method('getShippingAssignments');
        $this->quoteMock->expects($this->once())->method('isVirtual')->willReturn(true);
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturn($this->quoteMock);
        $this->quoteResourceModel->expects($this->once())->method('save')->with($this->quoteMock);
        $this->assertEquals($this->quoteMock, $this->saveHandler->save($this->quoteMock));
    }
}

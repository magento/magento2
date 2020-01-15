<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use Magento\Framework\Exception\InputException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\BillingAddressManagement;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\ShippingAddressAssignment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BillingAddressManagementTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var BillingAddressManagement
     */
    private $model;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var ShippingAddressAssignment|MockObject
     */
    private $shippingAddressAssignmentMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->quoteRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->shippingAddressAssignmentMock = $this->createPartialMock(
            ShippingAddressAssignment::class,
            ['setAddress']
        );
        $this->model = $this->objectManager->getObject(
            BillingAddressManagement::class,
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'shippingAddressAssignment' => $this->shippingAddressAssignmentMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetAddress()
    {
        $quoteMock = $this->createMock(Quote::class);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with('cartId')
            ->willReturn($quoteMock);

        $addressMock = $this->createMock(Address::class);
        $quoteMock->method('getBillingAddress')->willReturn($addressMock);

        $this->assertEquals($addressMock, $this->model->get('cartId'));
    }

    /**
     * @return void
     */
    public function testSetAddress()
    {
        $cartId = 100;
        $useForShipping = true;
        $addressId = 1;

        $address = $this->createPartialMock(Address::class, ['getId']);
        $quoteMock = $this->createPartialMock(
            Quote::class,
            ['removeAddress', 'getBillingAddress', 'setBillingAddress', 'setDataChanges']
        );

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($quoteMock);

        $address->expects($this->exactly(2))->method('getId')->willReturn($addressId);
        $quoteMock->expects($this->exactly(2))->method('getBillingAddress')->willReturn($address);
        $quoteMock->expects($this->once())->method('removeAddress')->with($addressId)->willReturnSelf();
        $quoteMock->expects($this->once())->method('setBillingAddress')->with($address)->willReturnSelf();
        $quoteMock->expects($this->once())->method('setDataChanges')->with(1)->willReturnSelf();

        $this->shippingAddressAssignmentMock->expects($this->once())
            ->method('setAddress')
            ->with($quoteMock, $address, $useForShipping);

        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quoteMock);
        $this->assertEquals($addressId, $this->model->assign($cartId, $address, $useForShipping));
    }

    /**
     * @return void
     */
    public function testSetAddressWithInabilityToSaveQuote()
    {
        $cartId = 100;
        $addressId = 1;

        $address = $this->createPartialMock(Address::class, ['getId']);
        $quoteMock = $this->createPartialMock(
            Quote::class,
            ['removeAddress', 'getBillingAddress', 'setBillingAddress', 'setDataChanges']
        );

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($quoteMock);

        $address->expects($this->once())->method('getId')->willReturn($addressId);
        $quoteMock->expects($this->once())->method('getBillingAddress')->willReturn($address);
        $quoteMock->expects($this->once())->method('removeAddress')->with($addressId)->willReturnSelf();
        $quoteMock->expects($this->once())->method('setBillingAddress')->with($address)->willReturnSelf();
        $quoteMock->expects($this->once())->method('setDataChanges')->with(1)->willReturnSelf();

        $this->shippingAddressAssignmentMock->expects($this->once())
            ->method('setAddress')
            ->with($quoteMock, $address, false);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($quoteMock)
            ->willThrowException(
                new \Exception('Some DB Error')
            );

        $this->expectException(InputException::class);
        $this->expectExceptionMessage('The address failed to save. Verify the address and try again.');
        $this->model->assign($cartId, $address);
    }
}

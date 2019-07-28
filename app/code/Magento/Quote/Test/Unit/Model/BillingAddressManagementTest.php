<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\BillingAddressManagement;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BillingAddressManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var BillingAddressManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingAssignmentMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->quoteRepositoryMock = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->validatorMock = $this->createMock(\Magento\Quote\Model\QuoteAddressValidator::class);
        $this->addressRepository = $this->createMock(\Magento\Customer\Api\AddressRepositoryInterface::class);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->model = $this->objectManager->getObject(
            \Magento\Quote\Model\BillingAddressManagement::class,
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'addressValidator' => $this->validatorMock,
                'logger' => $logger,
                'addressRepository' => $this->addressRepository
            ]
        );

        $this->shippingAssignmentMock = $this->createPartialMock(
            \Magento\Quote\Model\ShippingAddressAssignment::class,
            ['setAddress']
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $this->model,
            'shippingAddressAssignment',
            $this->shippingAssignmentMock
        );
    }

    /**
     * @return void
     */
    public function testGetAddress()
    {
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')
            ->with('cartId')->will($this->returnValue($quoteMock));

        $addressMock = $this->createMock(\Magento\Quote\Model\Quote\Address::class);
        $quoteMock->expects($this->any())->method('getBillingAddress')->will($this->returnValue($addressMock));

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

        $address = $this->createPartialMock(\Magento\Quote\Model\Quote\Address::class, ['getId']);
        $quoteMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
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

        $this->shippingAssignmentMock->expects($this->once())
            ->method('setAddress')
            ->with($quoteMock, $address, $useForShipping);

        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quoteMock);
        $this->assertEquals($addressId, $this->model->assign($cartId, $address, $useForShipping));
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage The address failed to save. Verify the address and try again.
     */
    public function testSetAddressWithInabilityToSaveQuote()
    {
        $cartId = 100;
        $addressId = 1;

        $address = $this->createPartialMock(\Magento\Quote\Model\Quote\Address::class, ['getId']);
        $quoteMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
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

        $this->shippingAssignmentMock->expects($this->once())
            ->method('setAddress')
            ->with($quoteMock, $address, false);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($quoteMock)
            ->willThrowException(
                new \Exception('Some DB Error')
            );
        $this->model->assign($cartId, $address);
    }
}

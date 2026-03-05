<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\BillingAddressManagement;
use Magento\Quote\Model\CartAddressMutexInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\QuoteAddressValidator;
use Magento\Quote\Model\ShippingAddressAssignment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BillingAddressManagementTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var BillingAddressManagement
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var MockObject
     */
    protected $validatorMock;

    /**
     * @var MockObject
     */
    protected $addressRepository;

    /**
     * @var MockObject
     */
    private $shippingAssignmentMock;

    /**
     * @var CartAddressMutexInterface
     */
    private MockObject|CartAddressMutexInterface $cartAddressMutex;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->quoteRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);
        $this->validatorMock = $this->createMock(QuoteAddressValidator::class);
        $this->addressRepository = $this->getMockForAbstractClass(AddressRepositoryInterface::class);
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->cartAddressMutex = $this->getMockForAbstractClass(CartAddressMutexInterface::class);

        $this->model = $this->objectManager->getObject(
            BillingAddressManagement::class,
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'addressValidator' => $this->validatorMock,
                'logger' => $logger,
                'addressRepository' => $this->addressRepository,
                'cartAddressMutex' => $this->cartAddressMutex
            ]
        );

        $this->shippingAssignmentMock = $this->createPartialMock(
            ShippingAddressAssignment::class,
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
        $quoteMock = $this->createMock(Quote::class);
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')
            ->with('cartId')->willReturn($quoteMock);

        $addressMock = $this->createMock(Address::class);
        $quoteMock->expects($this->any())->method('getBillingAddress')->willReturn($addressMock);

        $this->assertEquals($addressMock, $this->model->get('cartId'));
    }

    /**
     * @return void
     */
    public function testSetAddress()
    {
        $cartId = 100;
        $addressId = 1;
        $useForShipping = true;

        $quoteMock = $this->createPartialMock(
            Quote::class,
            ['getBillingAddress']
        );
        $address = $this->createPartialMock(Address::class, ['getId']);
        $address->expects($this->exactly(1))->method('getId')->willReturn($addressId);
        $quoteMock->expects($this->exactly(1))->method('getBillingAddress')->willReturn($address);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($quoteMock);

        $this->cartAddressMutex->expects($this->once())->method('execute')
            ->with(
                'cart_billing_address_lock_'.$addressId,
                \Closure::fromCallable([$this, 'assignAddressMethod']),
                $addressId,
                [$address, $quoteMock, $useForShipping]
            )
            ->willReturn($addressId);

        $this->assertEquals($addressId, $this->model->assign($cartId, $address, $useForShipping));
    }

    /**
     * @return void
     */
    public function testSetAddressWithInabilityToSaveQuote()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('The address failed to save. Verify the address and try again.');
        $addressId = 1;

        $address = $this->createPartialMock(Address::class, ['getId']);
        $quoteMock = $this->createPartialMock(
            Quote::class,
            ['removeAddress', 'getBillingAddress', 'setBillingAddress', 'setDataChanges']
        );

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
        $this->model->assignBillingAddress($address, $quoteMock, false);
    }

    /**
     * Assign address method.
     *
     * @param string $var
     * @return string
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function assignAddressMethod(string $var)
    {
        return $var;
    }

    /**
     * Set billing address test
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetBillingAddress()
    {
        $useForShipping = true;
        $addressId = 1;

        $address = $this->createPartialMock(Address::class, ['getId']);

        $quoteMock = $this->createPartialMock(
            Quote::class,
            ['removeAddress', 'getBillingAddress', 'setBillingAddress', 'setDataChanges']
        );

        $address->expects($this->exactly(2))->method('getId')->willReturn($addressId);
        $quoteMock->expects($this->exactly(2))->method('getBillingAddress')->willReturn($address);
        $quoteMock->expects($this->once())->method('removeAddress')->with($addressId)->willReturnSelf();
        $quoteMock->expects($this->once())->method('setBillingAddress')->with($address)->willReturnSelf();
        $quoteMock->expects($this->once())->method('setDataChanges')->with(1)->willReturnSelf();

        $this->shippingAssignmentMock->expects($this->once())
            ->method('setAddress')
            ->with($quoteMock, $address, $useForShipping);

        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quoteMock);
        $this->assertEquals($addressId, $this->model->assignBillingAddress($address, $quoteMock, $useForShipping));
    }
}

<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\GuestCart;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\GuestCart\GuestShippingAddressManagement;
use Magento\Quote\Model\GuestCart\GuestShippingAddressManagementInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GuestShippingAddressManagementTest extends TestCase
{
    /**
     * @var GuestShippingAddressManagementInterface
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $quoteAddressMock;

    /**
     * @var MockObject
     */
    protected $quoteIdMaskFactoryMock;

    /**
     * @var MockObject
     */
    protected $quoteIdMaskMock;

    /**
     * @var MockObject
     */
    protected $shippingAddressManagementMock;

    /**
     * @var string
     */
    protected $maskedCartId;

    /**
     * @var int
     */
    protected $cartId;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->shippingAddressManagementMock = $this->createMock(
            ShippingAddressManagementInterface::class
        );
        $this->quoteAddressMock = $this->createMock(Address::class);

        $this->maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $this->cartId = 123;

        $guestCartTestHelper = new GuestCartTestHelper($this);
        list($this->quoteIdMaskFactoryMock, $this->quoteIdMaskMock) = $guestCartTestHelper->mockQuoteIdMask(
            $this->maskedCartId,
            $this->cartId
        );

        $this->model = $objectManager->getObject(
            GuestShippingAddressManagement::class,
            [
                'shippingAddressManagement' => $this->shippingAddressManagementMock,
                'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock
            ]
        );
    }

    public function testAssign()
    {
        $addressId = 1;
        $this->shippingAddressManagementMock->expects($this->once())->method('assign')->willReturn($addressId);
        $this->assertEquals($addressId, $this->model->assign($this->maskedCartId, $this->quoteAddressMock));
    }

    public function testGet()
    {
        $this->shippingAddressManagementMock->expects($this->once())->method('get')->willReturn(
            $this->quoteAddressMock
        );
        $this->assertEquals($this->quoteAddressMock, $this->model->get($this->maskedCartId));
    }
}

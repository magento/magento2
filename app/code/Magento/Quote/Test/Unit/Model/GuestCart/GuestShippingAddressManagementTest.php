<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\GuestCart;

class GuestShippingAddressManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\GuestCart\GuestShippingAddressManagementInterface
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteAddressMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteIdMaskFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteIdMaskMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
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
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->shippingAddressManagementMock = $this->createMock(
            \Magento\Quote\Model\ShippingAddressManagementInterface::class
        );
        $this->quoteAddressMock = $this->createMock(\Magento\Quote\Model\Quote\Address::class);

        $this->maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $this->cartId = 123;

        $guestCartTestHelper = new GuestCartTestHelper($this);
        list($this->quoteIdMaskFactoryMock, $this->quoteIdMaskMock) = $guestCartTestHelper->mockQuoteIdMask(
            $this->maskedCartId,
            $this->cartId
        );

        $this->model = $objectManager->getObject(
            \Magento\Quote\Model\GuestCart\GuestShippingAddressManagement::class,
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

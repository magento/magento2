<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\GuestCart;

class GuestShippingAddressManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\GuestCart\GuestShippingAddressManagementInterface
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteAddressMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteIdMaskFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteIdMaskMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
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

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->shippingAddressManagementMock = $this->getMock(
            'Magento\Quote\Model\ShippingAddressManagementInterface',
            [],
            [],
            '',
            false
        );
        $this->quoteAddressMock = $this->getMock('Magento\Quote\Model\Quote\Address', [], [], '', false);

        $this->maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $this->cartId = 123;

        $guestCartTestHelper = new GuestCartTestHelper($this);
        list($this->quoteIdMaskFactoryMock, $this->quoteIdMaskMock) = $guestCartTestHelper->mockQuoteIdMask(
            $this->maskedCartId,
            $this->cartId
        );

        $this->model = $objectManager->getObject(
            'Magento\Quote\Model\GuestCart\GuestShippingAddressManagement',
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

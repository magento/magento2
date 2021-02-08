<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\GuestCart;

class GuestBillingAddressManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\GuestCart\GuestBillingAddressManagement
     */
    protected $model;

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
    protected $billingAddressManagementMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $addressMock;

    /**
     * @var string
     */
    protected $maskedCartId;

    /**
     * @var int
     */
    protected $cartId;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->addressMock = $this->createMock(\Magento\Quote\Model\Quote\Address::class);
        $this->billingAddressManagementMock = $this->createMock(
            \Magento\Quote\Api\BillingAddressManagementInterface::class
        );

        $this->maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $this->cartId = 123;

        $guestCartTestHelper = new GuestCartTestHelper($this);
        list($this->quoteIdMaskFactoryMock, $this->quoteIdMaskMock) = $guestCartTestHelper->mockQuoteIdMask(
            $this->maskedCartId,
            $this->cartId
        );

        $this->model = $objectManager->getObject(
            \Magento\Quote\Model\GuestCart\GuestBillingAddressManagement::class,
            [
                'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock,
                'billingAddressManagement' => $this->billingAddressManagementMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testGet()
    {
        $this->billingAddressManagementMock->expects($this->once())->method('get')->willReturn($this->addressMock);
        $this->assertEquals($this->addressMock, $this->model->get($this->maskedCartId));
    }

    /**
     * @return void
     */
    public function testAssign()
    {
        $addressId = 1;
        $this->billingAddressManagementMock->expects($this->once())->method('assign')->willReturn($addressId);
        $this->assertEquals($addressId, $this->model->assign($this->maskedCartId, $this->addressMock));
    }
}

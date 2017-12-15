<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\GuestCart;

class GuestPaymentMethodManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\GuestCart\GuestPaymentMethodManagement
     */
    protected $model;

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
    protected $paymentMethodManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

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
        $this->paymentMethodManagementMock = $this->createMock(
            \Magento\Quote\Api\PaymentMethodManagementInterface::class
        );
        $this->paymentMock = $this->createMock(\Magento\Quote\Model\Quote\Payment::class);

        $this->maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $this->cartId = 11;

        $guestCartTestHelper = new GuestCartTestHelper($this);
        list($this->quoteIdMaskFactoryMock, $this->quoteIdMaskMock) = $guestCartTestHelper->mockQuoteIdMask(
            $this->maskedCartId,
            $this->cartId
        );

        $this->model = $objectManager->getObject(
            \Magento\Quote\Model\GuestCart\GuestPaymentMethodManagement::class,
            [
                'paymentMethodManagement' => $this->paymentMethodManagementMock,
                'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock
            ]
        );
    }

    public function testGet()
    {
        $this->paymentMethodManagementMock->expects($this->once())->method('get')->willReturn($this->paymentMock);
        $this->assertEquals($this->paymentMock, $this->model->get($this->maskedCartId));
    }

    public function testGetList()
    {
        $paymentMethod = $this->createMock(\Magento\Quote\Api\Data\PaymentMethodInterface::class);
        $this->paymentMethodManagementMock->expects($this->once())->method('getList')->willReturn([$paymentMethod]);
        $this->assertEquals([$paymentMethod], $this->model->getList($this->maskedCartId));
    }

    public function testSetSimpleProduct()
    {
        $paymentId = 20;
        $this->paymentMethodManagementMock->expects($this->once())->method('set')->willReturn($paymentId);
        $this->assertEquals($paymentId, $this->model->set($this->maskedCartId, $this->paymentMock));
    }
}

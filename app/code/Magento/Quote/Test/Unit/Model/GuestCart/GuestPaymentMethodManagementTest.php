<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\GuestCart;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\GuestCart\GuestPaymentMethodManagement;
use Magento\Quote\Model\Quote\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GuestPaymentMethodManagementTest extends TestCase
{
    /**
     * @var GuestPaymentMethodManagement
     */
    protected $model;

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
    protected $paymentMethodManagementMock;

    /**
     * @var MockObject
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

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->paymentMethodManagementMock = $this->createMock(
            PaymentMethodManagementInterface::class
        );
        $this->paymentMock = $this->createMock(Payment::class);

        $this->maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $this->cartId = 11;

        $guestCartTestHelper = new GuestCartTestHelper($this);
        list($this->quoteIdMaskFactoryMock, $this->quoteIdMaskMock) = $guestCartTestHelper->mockQuoteIdMask(
            $this->maskedCartId,
            $this->cartId
        );

        $this->model = $objectManager->getObject(
            GuestPaymentMethodManagement::class,
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
        $paymentMethod = $this->getMockForAbstractClass(PaymentMethodInterface::class);
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

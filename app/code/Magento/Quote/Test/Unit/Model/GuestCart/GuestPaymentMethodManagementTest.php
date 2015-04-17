<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\GuestCart;

class GuestPaymentMethodManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\GuestCart\GuestPaymentMethodManagement
     */
    protected $model;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $methodListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $zeroTotalMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteIdMaskFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteIdMaskMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->quoteRepositoryMock = $this->getMock('Magento\Quote\Model\QuoteRepository', [], [], '', false);
        $this->methodListMock = $this->getMock('Magento\Payment\Model\MethodList', [], [], '', false);
        $this->zeroTotalMock = $this->getMock('Magento\Payment\Model\Checks\ZeroTotal', [], [], '', false);
        $this->quoteIdMaskFactoryMock = $this->getMock('Magento\Quote\Model\QuoteIdMaskFactory', [], [], '', false);
        $this->quoteIdMaskMock = $this->getMock('Magento\Quote\Model\QuoteIdMask', [], [], '', false);

        $this->model = $this->objectManager->getObject(
            'Magento\Quote\Model\GuestCart\GuestPaymentMethodManagement',
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'methodList' => $this->methodListMock,
                'zeroTotalValidator' => $this->zeroTotalMock,
                'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock
            ]
        );
    }

    public function testGetPaymentSuccess()
    {
        $maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $cartId = 11;

        $this->quoteIdMaskFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->once())
            ->method('load')
            ->with($maskedCartId, 'masked_id')
            ->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->once())
            ->method('getId')
            ->willReturn($cartId);

        $paymentMock = $this->getMock('Magento\Quote\Model\Quote\Payment', [], [], '', false);
        $paymentMock->expects($this->once())->method('getId')->will($this->returnValue(1));

        $quoteMock = $this->getMock('Magento\Quote\Model\Quote', [], [], '', false);
        $quoteMock->expects($this->once())->method('getPayment')->will($this->returnValue($paymentMock));

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->will($this->returnValue($quoteMock));
        $this->assertEquals($paymentMock, $this->model->get($maskedCartId));
    }

    public function testGetList()
    {
        $maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $cartId = 10;

        $this->quoteIdMaskFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->once())
            ->method('load')
            ->with($maskedCartId, 'masked_id')
            ->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->once())
            ->method('getId')
            ->willReturn($cartId);

        $quoteMock = $this->getMock('Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->will($this->returnValue($quoteMock));

        $paymentMethod = $this->getMock('Magento\Quote\Api\Data\PaymentMethodInterface');
        $this->methodListMock->expects($this->once())
            ->method('getAvailableMethods')
            ->with($quoteMock)
            ->will($this->returnValue([$paymentMethod]));
        $this->assertEquals([$paymentMethod], $this->model->getList($maskedCartId));
    }

    public function testSetSimpleProduct()
    {
        $maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $cartId = 100;
        $paymentId = 20;
        $methodData = ['method' => 'data'];
        $paymentMethod = 'checkmo';

        $this->quoteIdMaskFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->once())
            ->method('load')
            ->with($maskedCartId, 'masked_id')
            ->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->once())
            ->method('getId')
            ->willReturn($cartId);

        $quoteMock = $this->getMock(
            '\Magento\Quote\Model\Quote',
            ['getPayment', 'isVirtual', 'getShippingAddress', 'setTotalsCollectedFlag', 'collectTotals', 'save'],
            [],
            '',
            false
        );
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')->with($cartId)->willReturn($quoteMock);

        $methodMock = $this->getMock('Magento\Quote\Model\Quote\Payment', ['setChecks', 'getData'], [], '', false);
        $methodMock->expects($this->once())
            ->method('setChecks')
            ->with([
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_CHECKOUT,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
            ])
            ->willReturnSelf();
        $methodMock->expects($this->once())->method('getData')->willReturn($methodData);

        $paymentMock = $this->getMock(
            'Magento\Quote\Model\Quote\Payment',
            ['importData', 'getMethod', 'getMethodInstance', 'getId'],
            [],
            '',
            false
        );
        $paymentMock->expects($this->once())->method('importData')->with($methodData)->willReturnSelf();
        $paymentMock->expects($this->once())->method('getMethod')->willReturn($paymentMethod);

        $shippingAddressMock = $this->getMock(
            'Magento\Quote\Model\Quote\Address',
            ['getCountryId', 'setPaymentMethod'],
            [],
            '',
            false
        );
        $shippingAddressMock->expects($this->once())->method('getCountryId')->willReturn(100);
        $shippingAddressMock->expects($this->once())
            ->method('setPaymentMethod')
            ->with($paymentMethod)
            ->willReturnSelf();

        $quoteMock->expects($this->exactly(2))->method('getPayment')->willReturn($paymentMock);
        $quoteMock->expects($this->exactly(2))->method('isVirtual')->willReturn(false);
        $quoteMock->expects($this->exactly(4))->method('getShippingAddress')->willReturn($shippingAddressMock);

        $methodInstance = $this->getMock('\Magento\Payment\Model\Checks\PaymentMethodChecksInterface');
        $paymentMock->expects($this->once())->method('getMethodInstance')->willReturn($methodInstance);

        $this->zeroTotalMock->expects($this->once())
            ->method('isApplicable')
            ->with($methodInstance, $quoteMock)
            ->willReturn(true);

        $quoteMock->expects($this->once())->method('setTotalsCollectedFlag')->with(false)->willReturnSelf();
        $quoteMock->expects($this->once())->method('collectTotals')->willReturnSelf();
        $quoteMock->expects($this->once())->method('save')->willReturnSelf();

        $paymentMock->expects($this->once())->method('getId')->willReturn($paymentId);
        $this->assertEquals($paymentId, $this->model->set($maskedCartId, $methodMock));
    }
}

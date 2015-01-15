<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Service\V1\PaymentMethod;

class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReadService
     */
    protected $service;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodConverterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMethodConverterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $methodListMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->quoteRepositoryMock = $this->getMock('\Magento\Sales\Model\QuoteRepository', [], [], '', false);
        $this->quoteMethodConverterMock = $this->getMock(
            '\Magento\Checkout\Service\V1\Data\Cart\PaymentMethod\Converter', [], [], '', false
        );
        $this->paymentMethodConverterMock = $this->getMock(
            '\Magento\Checkout\Service\V1\Data\PaymentMethod\Converter', [], [], '', false
        );
        $this->methodListMock = $this->getMock('\Magento\Payment\Model\MethodList', [], [], '', false);

        $this->service = $this->objectManager->getObject(
            'Magento\Checkout\Service\V1\PaymentMethod\ReadService',
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'quoteMethodConverter' => $this->quoteMethodConverterMock,
                'paymentMethodConverter' => $this->paymentMethodConverterMock,
                'methodList' => $this->methodListMock,
            ]
        );
    }

    public function testGetPaymentIfPaymentMethodNotSet()
    {
        $cartId = 11;
        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $paymentMock = $this->getMock('\Magento\Sales\Model\Quote\Payment', [], [], '', false);
        $quoteMock->expects($this->once())->method('getPayment')->will($this->returnValue($paymentMock));
        $paymentMock->expects($this->once())->method('getId')->will($this->returnValue(null));

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->will($this->returnValue($quoteMock));

        $this->assertNull($this->service->getPayment($cartId));
    }

    public function testGetPaymentSuccess()
    {
        $cartId = 11;

        $paymentMock = $this->getMock('\Magento\Sales\Model\Quote\Payment', [], [], '', false);
        $paymentMock->expects($this->once())->method('getId')->will($this->returnValue(1));

        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $quoteMock->expects($this->once())->method('getPayment')->will($this->returnValue($paymentMock));

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->will($this->returnValue($quoteMock));

        $paymentMethodMock = $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\PaymentMethod', [], [], '', false);

        $this->quoteMethodConverterMock->expects($this->once())
            ->method('toDataObject')
            ->with($paymentMock)
            ->will($this->returnValue($paymentMethodMock));

        $this->assertEquals($paymentMethodMock, $this->service->getPayment($cartId));
    }

    public function testGetList()
    {
        $cartId = 10;
        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->will($this->returnValue($quoteMock));

        $methodList = [
            $this->getMock('\Magento\Payment\Model\MethodInterface'),
            $this->getMock('\Magento\Payment\Model\MethodInterface'),
        ];

        $this->methodListMock->expects($this->once())
            ->method('getAvailableMethods')
            ->with($quoteMock)
            ->will($this->returnValue($methodList));

        $paymentMethodMock = $this->getMock('\Magento\Checkout\Service\V1\Data\PaymentMethod', [], [], '', false);

        $this->paymentMethodConverterMock->expects($this->atLeastOnce())
            ->method('toDataObject')
            ->will($this->returnValue($paymentMethodMock));

        $expectedResult = [
            $this->getMock('\Magento\Checkout\Service\V1\Data\PaymentMethod', [], [], '', false),
            $this->getMock('\Magento\Checkout\Service\V1\Data\PaymentMethod', [], [], '', false),
        ];

        $this->assertEquals($expectedResult, $this->service->getList($cartId));
    }
}

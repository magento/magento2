<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Service\V1\PaymentMethod;

class WriteServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WriteService
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
    protected $paymentMethodBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $methodListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->quoteRepositoryMock = $this->getMock('\Magento\Sales\Model\QuoteRepository', [], [], '', false);
        $this->paymentMethodBuilderMock = $this->getMock(
            '\Magento\Checkout\Service\V1\Data\Cart\PaymentMethod\Builder', [], [], '', false
        );
        $this->methodListMock = $this->getMock('\Magento\Payment\Model\MethodList', [], [], '', false);
        $this->validatorMock = $this->getMock('\Magento\Payment\Model\Checks\ZeroTotal', [], [], '', false);

        $this->service = $this->objectManager->getObject(
            'Magento\Checkout\Service\V1\PaymentMethod\WriteService',
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'paymentMethodBuilder' => $this->paymentMethodBuilderMock,
                'methodList' => $this->methodListMock,
                'zeroTotalValidator' => $this->validatorMock,
            ]
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Billing address is not set
     */
    public function testSetVirtualQuotePaymentThrowsExceptionIfBillingAdressNotSet()
    {
        $cartId = 11;

        $paymentsCollectionMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Collection\AbstractCollection', [], [], '', false
        );

        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $quoteMock->expects($this->any())
            ->method('getPaymentsCollection')
            ->will($this->returnValue($paymentsCollectionMock));
        $quoteMock->expects($this->any())->method('isVirtual')->will($this->returnValue(true));

        $billingAddressMock = $this->getMock('\Magento\Sales\Model\Quote\Address', [], [], '', false);
        $quoteMock->expects($this->any())->method('getBillingAddress')->will($this->returnValue($billingAddressMock));

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->will($this->returnValue($quoteMock));

        $paymentMethodMock = $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\PaymentMethod', [], [], '', false);

        $this->service->set($paymentMethodMock, $cartId);
    }

    public function testSetVirtualQuotePaymentSuccess()
    {
        $cartId = 11;
        $paymentId = 13;
        $paymentsCollectionMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Collection\AbstractCollection', [], [], '', false
        );

        $quoteMock = $this->getMock(
            '\Magento\Sales\Model\Quote',
            [
                'setTotalsCollectedFlag', '__wakeup', 'getPaymentsCollection', 'getPayment',
                'getItemsCollection', 'isVirtual', 'getBillingAddress', 'collectTotals', 'save'
            ], [], '', false
        );
        $quoteMock->expects($this->any())
            ->method('getPaymentsCollection')
            ->will($this->returnValue($paymentsCollectionMock));
        $quoteMock->expects($this->any())->method('isVirtual')->will($this->returnValue(true));

        $billingAddressMock =
            $this->getMock('\Magento\Sales\Model\Quote\Address', ['getCountryId', '__wakeup'], [], '', false);
        $billingAddressMock->expects($this->once())->method('getCountryId')->will($this->returnValue(1));
        $quoteMock->expects($this->any())->method('getBillingAddress')->will($this->returnValue($billingAddressMock));

        $quoteMock->expects($this->once())->method('setTotalsCollectedFlag')->will($this->returnSelf());
        $quoteMock->expects($this->once())->method('collectTotals')->will($this->returnSelf());

        $paymentMock = $this->getMock('Magento\Sales\Model\Quote\Payment', [], [], '', false);
        $paymentMock->expects($this->once())->method('getId')->will($this->returnValue($paymentId));

        $methodMock = $this->getMockForAbstractClass(
            '\Magento\Payment\Model\Method\AbstractMethod', [], '', false, false
        );

        $paymentMock->expects($this->once())->method('getMethodInstance')->will($this->returnValue($methodMock));

        $quoteMock->expects($this->once())->method('getPayment')->will($this->returnValue($paymentMock));

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->will($this->returnValue($quoteMock));

        $paymentMethodMock = $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\PaymentMethod', [], [], '', false);
        $this->validatorMock->expects($this->once())->method('isApplicable')
            ->with($methodMock, $quoteMock)->will($this->returnValue(true));

        $this->paymentMethodBuilderMock->expects($this->once())
            ->method('build')
            ->with($paymentMethodMock, $quoteMock)
            ->will($this->returnValue($paymentMock));

        $this->assertEquals($paymentId, $this->service->set($paymentMethodMock, $cartId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InvalidTransitionException
     * @expectedExceptionMessage The requested Payment Method is not available.
     */
    public function testSetVirtualQuotePaymentFail()
    {
        $cartId = 11;

        $paymentsCollectionMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Collection\AbstractCollection', [], [], '', false
        );

        $quoteMock = $this->getMock(
            '\Magento\Sales\Model\Quote',
            [
                'setTotalsCollectedFlag', '__wakeup', 'getPaymentsCollection', 'getPayment',
                'getItemsCollection', 'isVirtual', 'getBillingAddress', 'collectTotals'
            ], [], '', false
        );
        $quoteMock->expects($this->any())
            ->method('getPaymentsCollection')
            ->will($this->returnValue($paymentsCollectionMock));
        $quoteMock->expects($this->any())->method('isVirtual')->will($this->returnValue(true));

        $billingAddressMock =
            $this->getMock('\Magento\Sales\Model\Quote\Address', ['getCountryId', '__wakeup'], [], '', false);
        $billingAddressMock->expects($this->once())->method('getCountryId')->will($this->returnValue(1));
        $quoteMock->expects($this->any())->method('getBillingAddress')->will($this->returnValue($billingAddressMock));

        $quoteMock->expects($this->never())->method('setTotalsCollectedFlag');
        $quoteMock->expects($this->never())->method('collectTotals');

        $paymentMock = $this->getMock('Magento\Sales\Model\Quote\Payment', [], [], '', false);
        $paymentMock->expects($this->never())->method('getId');

        $methodMock = $this->getMockForAbstractClass(
            '\Magento\Payment\Model\Method\AbstractMethod', [], '', false, false
        );

        $paymentMock->expects($this->once())->method('getMethodInstance')->will($this->returnValue($methodMock));

        $quoteMock->expects($this->never())->method('getPayment');

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->will($this->returnValue($quoteMock));

        $paymentMethodMock = $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\PaymentMethod', [], [], '', false);
        $this->validatorMock->expects($this->once())->method('isApplicable')
            ->with($methodMock, $quoteMock)->will($this->returnValue(false));

        $this->paymentMethodBuilderMock->expects($this->once())
            ->method('build')
            ->with($paymentMethodMock, $quoteMock)
            ->will($this->returnValue($paymentMock));

        $this->service->set($paymentMethodMock, $cartId);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Shipping address is not set
     */
    public function testSetNotVirtualQuotePaymentThrowsExceptionIfShippingAddressNotSet()
    {
        $cartId = 11;
        $quoteMock = $this->getMock(
            '\Magento\Sales\Model\Quote',
            ['__wakeup', 'getPaymentsCollection', 'isVirtual', 'getShippingAddress'], [], '', false
        );

        $paymentsCollectionMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Collection\AbstractCollection', [], [], '', false
        );

        $quoteMock->expects($this->any())
            ->method('getPaymentsCollection')
            ->will($this->returnValue($paymentsCollectionMock));
        $quoteMock->expects($this->any())->method('isVirtual')->will($this->returnValue(false));
        $quoteMock->expects($this->any())
            ->method('getShippingAddress')
            ->will($this->returnValue($this->getMock('\Magento\Sales\Model\Quote\Address', [], [], '', false)));

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->will($this->returnValue($quoteMock));

        $paymentMethodMock = $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\PaymentMethod', [], [], '', false);
        $paymentMock = $this->getMock('Magento\Sales\Model\Quote\Payment', [], [], '', false);

        $this->paymentMethodBuilderMock->expects($this->once())
            ->method('build')
            ->with($paymentMethodMock, $quoteMock)
            ->will($this->returnValue($paymentMock));

        $this->service->set($paymentMethodMock, $cartId);
    }

    public function testSetNotVirtualQuotePaymentSuccess()
    {
        $cartId = 11;
        $paymentId = 13;

        $paymentsCollectionMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Collection\AbstractCollection', [], [], '', false
        );

        $quoteMock = $this->getMock(
            '\Magento\Sales\Model\Quote',
            [
                'setTotalsCollectedFlag', '__wakeup', 'getPaymentsCollection', 'getPayment',
                'getItemsCollection', 'isVirtual', 'getShippingAddress', 'collectTotals', 'save'
            ], [], '', false
        );
        $quoteMock->expects($this->any())
            ->method('getPaymentsCollection')
            ->will($this->returnValue($paymentsCollectionMock));
        $quoteMock->expects($this->any())->method('isVirtual')->will($this->returnValue(false));

        $shippingAddressMock =
            $this->getMock('\Magento\Sales\Model\Quote\Address', ['getCountryId', '__wakeup'], [], '', false);
        $shippingAddressMock->expects($this->once())->method('getCountryId')->will($this->returnValue(1));
        $quoteMock->expects($this->any())->method('getShippingAddress')->will($this->returnValue($shippingAddressMock));

        $quoteMock->expects($this->once())->method('setTotalsCollectedFlag')->will($this->returnSelf());
        $quoteMock->expects($this->once())->method('collectTotals')->will($this->returnSelf());

        $paymentMock = $this->getMock('Magento\Sales\Model\Quote\Payment', [], [], '', false);
        $paymentMock->expects($this->once())->method('getId')->will($this->returnValue($paymentId));

        $methodMock = $this->getMockForAbstractClass(
            '\Magento\Payment\Model\Method\AbstractMethod', [], '', false, false
        );
        $paymentMock->expects($this->once())->method('getMethodInstance')->will($this->returnValue($methodMock));
        $this->validatorMock->expects($this->once())->method('isApplicable')
            ->with($methodMock, $quoteMock)->will($this->returnValue(true));

        $quoteMock->expects($this->once())->method('getPayment')->will($this->returnValue($paymentMock));

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->will($this->returnValue($quoteMock));

        $paymentMethodMock = $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\PaymentMethod', [], [], '', false);

        $this->paymentMethodBuilderMock->expects($this->once())
            ->method('build')
            ->with($paymentMethodMock, $quoteMock)
            ->will($this->returnValue($paymentMock));

        $this->assertEquals($paymentId, $this->service->set($paymentMethodMock, $cartId));
    }
}

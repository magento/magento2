<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

class PaymentMethodManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\PaymentMethodManagement
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

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->quoteRepositoryMock = $this->getMockForAbstractClass(
            \Magento\Quote\Api\CartRepositoryInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->methodListMock = $this->createMock(\Magento\Payment\Model\MethodList::class);
        $this->zeroTotalMock = $this->createMock(\Magento\Payment\Model\Checks\ZeroTotal::class);

        $this->model = $this->objectManager->getObject(
            \Magento\Quote\Model\PaymentMethodManagement::class,
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'methodList' => $this->methodListMock,
                'zeroTotalValidator' => $this->zeroTotalMock
            ]
        );
    }

    public function testGetPaymentIfPaymentMethodNotSet()
    {
        $cartId = 11;
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $paymentMock = $this->createMock(\Magento\Quote\Model\Quote\Payment::class);
        $quoteMock->expects($this->once())->method('getPayment')->will($this->returnValue($paymentMock));
        $paymentMock->expects($this->once())->method('getId')->will($this->returnValue(null));

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->will($this->returnValue($quoteMock));

        $this->assertNull($this->model->get($cartId));
    }

    public function testGetPaymentSuccess()
    {
        $cartId = 11;

        $paymentMock = $this->createMock(\Magento\Quote\Model\Quote\Payment::class);
        $paymentMock->expects($this->once())->method('getId')->will($this->returnValue(1));

        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $quoteMock->expects($this->once())->method('getPayment')->will($this->returnValue($paymentMock));

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->will($this->returnValue($quoteMock));
        $this->assertEquals($paymentMock, $this->model->get($cartId));
    }

    public function testGetList()
    {
        $cartId = 10;
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->will($this->returnValue($quoteMock));

        $paymentMethod = $this->createMock(\Magento\Quote\Api\Data\PaymentMethodInterface::class);
        $this->methodListMock->expects($this->once())
            ->method('getAvailableMethods')
            ->with($quoteMock)
            ->will($this->returnValue([$paymentMethod]));
        $this->assertEquals([$paymentMethod], $this->model->getList($cartId));
    }

    public function testSetVirtualProduct()
    {
        $cartId = 100;
        $paymentId = 200;
        $methodDataWithAdditionalData = ['method' => 'data', 'additional_data' => ['additional' => 'value']];
        $methodData = $methodDataWithAdditionalData;
        $paymentMethod = 'checkmo';

        $quoteMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            ['setTotalsCollectedFlag', 'getPayment', 'isVirtual', 'getBillingAddress', 'collectTotals', 'save']
        );
        $this->quoteRepositoryMock->expects($this->once())->method('get')->with($cartId)->willReturn($quoteMock);

        $methodMock = $this->createPartialMock(\Magento\Quote\Model\Quote\Payment::class, ['setChecks', 'getData']);
        $methodMock->expects($this->once())
            ->method('setChecks')
            ->with(
                [
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_CHECKOUT,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                ]
            )
            ->willReturnSelf();
        $methodMock->expects($this->once())->method('getData')->willReturn($methodDataWithAdditionalData);

        $paymentMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Payment::class,
            ['importData', 'getMethod', 'getMethodInstance', 'getId']
        );
        $paymentMock->expects($this->once())->method('importData')->with($methodData)->willReturnSelf();
        $paymentMock->expects($this->once())->method('getMethod')->willReturn($paymentMethod);

        $billingAddressMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address::class,
            ['getCountryId', 'setPaymentMethod']
        );
        $billingAddressMock->expects($this->once())
            ->method('setPaymentMethod')
            ->with($paymentMethod)
            ->willReturnSelf();

        $quoteMock->method('getPayment')->willReturn($paymentMock);
        $quoteMock->expects($this->once())->method('isVirtual')->willReturn(true);
        $quoteMock->expects($this->once())->method('getBillingAddress')->willReturn($billingAddressMock);

        $methodInstance = $this->getMockForAbstractClass(\Magento\Payment\Model\MethodInterface::class);
        $paymentMock->expects($this->once())->method('getMethodInstance')->willReturn($methodInstance);

        $this->zeroTotalMock->expects($this->once())
            ->method('isApplicable')
            ->with($methodInstance, $quoteMock)
            ->willReturn(true);

        $quoteMock->expects($this->once())->method('setTotalsCollectedFlag')->with(false)->willReturnSelf();
        $quoteMock->expects($this->once())->method('save')->willReturnSelf();

        $paymentMock->expects($this->once())->method('getId')->willReturn($paymentId);
        $this->assertEquals($paymentId, $this->model->set($cartId, $methodMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InvalidTransitionException
     * @expectedExceptionMessage The requested Payment Method is not available.
     */
    public function testSetVirtualProductThrowsExceptionIfPaymentMethodNotAvailable()
    {
        $cartId = 100;
        $methodData = ['method' => 'data'];
        $paymentMethod = 'checkmo';

        $quoteMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            ['getPayment', 'isVirtual', 'getBillingAddress']
        );
        $this->quoteRepositoryMock->expects($this->once())->method('get')->with($cartId)->willReturn($quoteMock);

        $methodMock = $this->createPartialMock(\Magento\Quote\Model\Quote\Payment::class, ['setChecks', 'getData']);
        $methodMock->expects($this->once())
            ->method('setChecks')
            ->with(
                [
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_CHECKOUT,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                ]
            )
            ->willReturnSelf();
        $methodMock->expects($this->once())->method('getData')->willReturn($methodData);

        $paymentMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Payment::class,
            ['importData', 'getMethod', 'getMethodInstance']
        );
        $paymentMock->expects($this->once())->method('importData')->with($methodData)->willReturnSelf();
        $paymentMock->expects($this->once())->method('getMethod')->willReturn($paymentMethod);

        $billingAddressMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address::class,
            ['getCountryId', 'setPaymentMethod']
        );
        $billingAddressMock->expects($this->once())
            ->method('setPaymentMethod')
            ->with($paymentMethod)
            ->willReturnSelf();

        $quoteMock->method('getPayment')->willReturn($paymentMock);
        $quoteMock->method('isVirtual')->willReturn(true);
        $quoteMock->method('getBillingAddress')->willReturn($billingAddressMock);

        $methodInstance = $this->getMockForAbstractClass(\Magento\Payment\Model\MethodInterface::class);
        $paymentMock->expects($this->once())->method('getMethodInstance')->willReturn($methodInstance);

        $this->zeroTotalMock->expects($this->once())
            ->method('isApplicable')
            ->with($methodInstance, $quoteMock)
            ->willReturn(false);
        $this->model->set($cartId, $methodMock);
    }

    public function testSetSimpleProduct()
    {
        $cartId = 100;
        $paymentId = 20;
        $methodData = ['method' => 'data'];
        $paymentMethod = 'checkmo';

        $quoteMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            ['getPayment', 'isVirtual', 'getShippingAddress', 'setTotalsCollectedFlag', 'collectTotals', 'save']
        );
        $this->quoteRepositoryMock->expects($this->once())->method('get')->with($cartId)->willReturn($quoteMock);

        $methodMock = $this->createPartialMock(\Magento\Quote\Model\Quote\Payment::class, ['setChecks', 'getData']);
        $methodMock->expects($this->once())
            ->method('setChecks')
            ->with(
                [
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_CHECKOUT,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                ]
            )
            ->willReturnSelf();
        $methodMock->expects($this->once())->method('getData')->willReturn($methodData);

        $paymentMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Payment::class,
            ['importData', 'getMethod', 'getMethodInstance', 'getId']
        );
        $paymentMock->expects($this->once())->method('importData')->with($methodData)->willReturnSelf();
        $paymentMock->expects($this->once())->method('getMethod')->willReturn($paymentMethod);

        $shippingAddressMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address::class,
            ['getCountryId', 'setPaymentMethod', 'setCollectShippingRates']
        );
        $shippingAddressMock->expects($this->once())->method('getCountryId')->willReturn(100);
        $shippingAddressMock->expects($this->once())
            ->method('setPaymentMethod')
            ->with($paymentMethod)
            ->willReturnSelf();
        $shippingAddressMock->expects($this->once())
            ->method('setCollectShippingRates')
            ->with(true);

        $quoteMock->method('getPayment')->willReturn($paymentMock);
        $quoteMock->method('isVirtual')->willReturn(false);
        $quoteMock->method('getShippingAddress')->willReturn($shippingAddressMock);

        $methodInstance = $this->getMockForAbstractClass(\Magento\Payment\Model\MethodInterface::class);
        $paymentMock->expects($this->once())->method('getMethodInstance')->willReturn($methodInstance);

        $this->zeroTotalMock->expects($this->once())
            ->method('isApplicable')
            ->with($methodInstance, $quoteMock)
            ->willReturn(true);

        $quoteMock->expects($this->once())->method('setTotalsCollectedFlag')->with(false)->willReturnSelf();
        $quoteMock->expects($this->once())->method('save')->willReturnSelf();

        $paymentMock->expects($this->once())->method('getId')->willReturn($paymentId);
        $this->assertEquals($paymentId, $this->model->set($cartId, $methodMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InvalidTransitionException
     * @expectedExceptionMessage The shipping address is missing. Set the address and try again.
     */
    public function testSetSimpleProductTrowsExceptionIfShippingAddressNotSet()
    {
        $cartId = 100;

        $quoteMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            ['getPayment', 'isVirtual', 'getShippingAddress']
        );
        $this->quoteRepositoryMock->expects($this->once())->method('get')->with($cartId)->willReturn($quoteMock);

        /** @var \Magento\Quote\Model\Quote\Payment|\PHPUnit_Framework_MockObject_MockObject $methodMock */
        $methodMock = $this->createPartialMock(\Magento\Quote\Model\Quote\Payment::class, ['setChecks', 'getData']);
        $methodMock->expects($this->once())
            ->method('setChecks')
            ->with([
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_CHECKOUT,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
            ])
            ->willReturnSelf();
        $methodMock->expects($this->never())->method('getData');

        $shippingAddressMock = $this->createPartialMock(\Magento\Quote\Model\Quote\Address::class, ['getCountryId']);
        $shippingAddressMock->expects($this->once())->method('getCountryId')->willReturn(null);

        $quoteMock->method('isVirtual')->willReturn(false);
        $quoteMock->method('getShippingAddress')->willReturn($shippingAddressMock);

        $this->model->set($cartId, $methodMock);
    }
}

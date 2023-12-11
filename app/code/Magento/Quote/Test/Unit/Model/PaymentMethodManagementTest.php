<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Model\Checks\ZeroTotal;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Model\MethodList;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use Magento\Quote\Model\PaymentMethodManagement;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentMethodManagementTest extends TestCase
{
    /**
     * @var PaymentMethodManagement
     */
    protected $model;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var MockObject
     */
    protected $methodListMock;

    /**
     * @var MockObject
     */
    protected $zeroTotalMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->quoteRepositoryMock = $this->getMockForAbstractClass(
            CartRepositoryInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->methodListMock = $this->createMock(MethodList::class);
        $this->zeroTotalMock = $this->createMock(ZeroTotal::class);

        $this->model = $this->objectManager->getObject(
            PaymentMethodManagement::class,
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
        $quoteMock = $this->createMock(Quote::class);
        $paymentMock = $this->createMock(Payment::class);
        $quoteMock->expects($this->once())->method('getPayment')->willReturn($paymentMock);
        $paymentMock->expects($this->once())->method('getId')->willReturn(null);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->willReturn($quoteMock);

        $this->assertNull($this->model->get($cartId));
    }

    public function testGetPaymentSuccess()
    {
        $cartId = 11;

        $paymentMock = $this->createMock(Payment::class);
        $paymentMock->expects($this->once())->method('getId')->willReturn(1);

        $quoteMock = $this->createMock(Quote::class);
        $quoteMock->expects($this->once())->method('getPayment')->willReturn($paymentMock);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->willReturn($quoteMock);
        $this->assertEquals($paymentMock, $this->model->get($cartId));
    }

    public function testGetList()
    {
        $cartId = 10;
        $quoteMock = $this->createMock(Quote::class);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->willReturn($quoteMock);

        $paymentMethod = $this->getMockForAbstractClass(PaymentMethodInterface::class);
        $this->methodListMock->expects($this->once())
            ->method('getAvailableMethods')
            ->with($quoteMock)
            ->willReturn([$paymentMethod]);
        $this->assertEquals([$paymentMethod], $this->model->getList($cartId));
    }

    public function testSetVirtualProduct()
    {
        $cartId = 100;
        $paymentId = 200;
        $methodDataWithAdditionalData = ['method' => 'data', 'additional_data' => ['additional' => 'value']];
        $methodData = $methodDataWithAdditionalData;
        $paymentMethod = 'checkmo';

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['setTotalsCollectedFlag'])
            ->onlyMethods(['getPayment', 'isVirtual', 'getBillingAddress', 'collectTotals', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteRepositoryMock->expects($this->once())->method('get')->with($cartId)->willReturn($quoteMock);

        $methodMock = $this->getMockBuilder(Payment::class)
            ->addMethods(['setChecks'])
            ->onlyMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $methodMock->expects($this->once())
            ->method('setChecks')
            ->with(
                [
                    AbstractMethod::CHECK_USE_CHECKOUT,
                    AbstractMethod::CHECK_USE_FOR_COUNTRY,
                    AbstractMethod::CHECK_USE_FOR_CURRENCY,
                    AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                ]
            )
            ->willReturnSelf();
        $methodMock->expects($this->once())->method('getData')->willReturn($methodDataWithAdditionalData);

        $paymentMock = $this->createPartialMock(
            Payment::class,
            ['importData', 'getMethod', 'getMethodInstance', 'getId']
        );
        $paymentMock->expects($this->once())->method('importData')->with($methodData)->willReturnSelf();
        $paymentMock->expects($this->once())->method('getMethod')->willReturn($paymentMethod);

        $billingAddressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['setPaymentMethod'])
            ->onlyMethods(['getCountryId'])
            ->disableOriginalConstructor()
            ->getMock();
        $billingAddressMock->expects($this->once())
            ->method('setPaymentMethod')
            ->with($paymentMethod)
            ->willReturnSelf();

        $quoteMock->method('getPayment')->willReturn($paymentMock);
        $quoteMock->expects($this->once())->method('isVirtual')->willReturn(true);
        $quoteMock->expects($this->once())->method('getBillingAddress')->willReturn($billingAddressMock);

        $methodInstance = $this->getMockForAbstractClass(MethodInterface::class);
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

    public function testSetVirtualProductThrowsExceptionIfPaymentMethodNotAvailable()
    {
        $this->expectException('Magento\Framework\Exception\State\InvalidTransitionException');
        $this->expectExceptionMessage('The requested Payment Method is not available.');
        $cartId = 100;
        $methodData = ['method' => 'data'];
        $paymentMethod = 'checkmo';

        $quoteMock = $this->createPartialMock(
            Quote::class,
            ['getPayment', 'isVirtual', 'getBillingAddress']
        );
        $this->quoteRepositoryMock->expects($this->once())->method('get')->with($cartId)->willReturn($quoteMock);

        $methodMock = $this->getMockBuilder(Payment::class)
            ->addMethods(['setChecks'])
            ->onlyMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $methodMock->expects($this->once())
            ->method('setChecks')
            ->with(
                [
                    AbstractMethod::CHECK_USE_CHECKOUT,
                    AbstractMethod::CHECK_USE_FOR_COUNTRY,
                    AbstractMethod::CHECK_USE_FOR_CURRENCY,
                    AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                ]
            )
            ->willReturnSelf();
        $methodMock->expects($this->once())->method('getData')->willReturn($methodData);

        $paymentMock = $this->createPartialMock(
            Payment::class,
            ['importData', 'getMethod', 'getMethodInstance']
        );
        $paymentMock->expects($this->once())->method('importData')->with($methodData)->willReturnSelf();
        $paymentMock->expects($this->once())->method('getMethod')->willReturn($paymentMethod);

        $billingAddressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['setPaymentMethod'])
            ->onlyMethods(['getCountryId'])
            ->disableOriginalConstructor()
            ->getMock();
        $billingAddressMock->expects($this->once())
            ->method('setPaymentMethod')
            ->with($paymentMethod)
            ->willReturnSelf();

        $quoteMock->method('getPayment')->willReturn($paymentMock);
        $quoteMock->method('isVirtual')->willReturn(true);
        $quoteMock->method('getBillingAddress')->willReturn($billingAddressMock);

        $methodInstance = $this->getMockForAbstractClass(MethodInterface::class);
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

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['setTotalsCollectedFlag'])
            ->onlyMethods(['getPayment', 'isVirtual', 'getShippingAddress', 'collectTotals', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteRepositoryMock->expects($this->once())->method('get')->with($cartId)->willReturn($quoteMock);

        $methodMock = $this->getMockBuilder(Payment::class)
            ->addMethods(['setChecks'])
            ->onlyMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $methodMock->expects($this->once())
            ->method('setChecks')
            ->with(
                [
                    AbstractMethod::CHECK_USE_CHECKOUT,
                    AbstractMethod::CHECK_USE_FOR_COUNTRY,
                    AbstractMethod::CHECK_USE_FOR_CURRENCY,
                    AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                ]
            )
            ->willReturnSelf();
        $methodMock->expects($this->once())->method('getData')->willReturn($methodData);

        $paymentMock = $this->createPartialMock(
            Payment::class,
            ['importData', 'getMethod', 'getMethodInstance', 'getId']
        );
        $paymentMock->expects($this->once())->method('importData')->with($methodData)->willReturnSelf();
        $paymentMock->expects($this->once())->method('getMethod')->willReturn($paymentMethod);

        $shippingAddressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['setPaymentMethod', 'setCollectShippingRates'])
            ->onlyMethods(['getCountryId'])
            ->disableOriginalConstructor()
            ->getMock();
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

        $methodInstance = $this->getMockForAbstractClass(MethodInterface::class);
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

    public function testSetSimpleProductTrowsExceptionIfShippingAddressNotSet()
    {
        $this->expectException('Magento\Framework\Exception\State\InvalidTransitionException');
        $this->expectExceptionMessage('The shipping address is missing. Set the address and try again.');
        $cartId = 100;

        $quoteMock = $this->createPartialMock(
            Quote::class,
            ['getPayment', 'isVirtual', 'getShippingAddress']
        );
        $this->quoteRepositoryMock->expects($this->once())->method('get')->with($cartId)->willReturn($quoteMock);

        /** @var \Magento\Quote\Model\Quote\Payment|MockObject $methodMock */
        $methodMock = $this->getMockBuilder(Payment::class)
            ->addMethods(['setChecks'])
            ->onlyMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $methodMock->expects($this->once())
            ->method('setChecks')
            ->with([
                AbstractMethod::CHECK_USE_CHECKOUT,
                AbstractMethod::CHECK_USE_FOR_COUNTRY,
                AbstractMethod::CHECK_USE_FOR_CURRENCY,
                AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
            ])
            ->willReturnSelf();
        $methodMock->expects($this->never())->method('getData');

        $shippingAddressMock = $this->createPartialMock(Address::class, ['getCountryId']);
        $shippingAddressMock->expects($this->once())->method('getCountryId')->willReturn(null);

        $quoteMock->method('isVirtual')->willReturn(false);
        $quoteMock->method('getShippingAddress')->willReturn($shippingAddressMock);

        $this->model->set($cartId, $methodMock);
    }
}

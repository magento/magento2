<?php

namespace Magento\InstantPurchase\Test\Unit\Model;

use Magento\InstantPurchase\Model\InstantPurchaseOption;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Customer\Model\Address;
use Magento\Quote\Api\Data\ShippingMethodInterface;

class InstantPurchaseOptionTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var PaymentTokenInterface|MockObject
     */
    private $paymentTokenMock;

    /**
     * @var Address|MockObject
     */
    private $addressMock;

    /**
     * @var ShippingMethodInterface|MockObject
     */
    private $shippingMethodMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->paymentTokenMock = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMock();
        $this->addressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shippingMethodMock = $this->getMockBuilder(ShippingMethodInterface::class)
            ->getMock();
    }

    /**
     * @param bool $available
     * @dataProvider isAvailableDataProvider
     */
    public function testIsAvailable($available)
    {
        $option = $this->objectManager->getObject(
            InstantPurchaseOption::class,
            [
                'paymentToken' => $this->paymentTokenMock,
                'shippingAddress' => $this->addressMock,
                'billingAddress' => $this->addressMock,
                'shippingMethod' => $this->shippingMethodMock,
            ]
        );

        $this->shippingMethodMock->expects($this->once())
            ->method('getAvailable')
            ->willReturn($available);

        $this->assertEquals($available, $option->isAvailable());
    }

    public function isAvailableDataProvider()
    {
        return [
            'available' => [true],
            'unavailable' => [false],
        ];
    }

    public function testGetPaymentToken()
    {
        $option = $this->objectManager->getObject(
            InstantPurchaseOption::class,
            [
                'paymentToken' => $this->paymentTokenMock,
            ]
        );

        $this->assertInstanceOf(PaymentTokenInterface::class, $option->getPaymentToken());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testGetPaymentTokenException()
    {
        $option = $this->objectManager->getObject(
            InstantPurchaseOption::class,
            [
                'paymentToken' => null,
            ]
        );

        $this->assertInstanceOf(PaymentTokenInterface::class, $option->getPaymentToken());
    }

    public function testGetShippingAddress()
    {
        $option = $this->objectManager->getObject(
            InstantPurchaseOption::class,
            [
                'shippingAddress' => $this->addressMock,
            ]
        );

        $this->assertInstanceOf(Address::class, $option->getShippingAddress());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testGetShippingAddressException()
    {
        $option = $this->objectManager->getObject(
            InstantPurchaseOption::class,
            [
                'shippingAddress' => null,
            ]
        );

        $this->assertInstanceOf(Address::class, $option->getShippingAddress());
    }

    public function testGetBillingAddress()
    {
        $option = $this->objectManager->getObject(
            InstantPurchaseOption::class,
            [
                'billingAddress' => $this->addressMock,
            ]
        );

        $this->assertInstanceOf(Address::class, $option->getBillingAddress());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testGetBillingAddressException()
    {
        $option = $this->objectManager->getObject(
            InstantPurchaseOption::class,
            [
                'billingAddress' => null,
            ]
        );

        $this->assertInstanceOf(Address::class, $option->getBillingAddress());
    }

    public function testGetShippingMethod()
    {
        $option = $this->objectManager->getObject(
            InstantPurchaseOption::class,
            [
                'shippingMethod' => $this->shippingMethodMock,
            ]
        );

        $this->assertInstanceOf(ShippingMethodInterface::class, $option->getShippingMethod());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testGetShippingMethodException()
    {
        $option = $this->objectManager->getObject(
            InstantPurchaseOption::class,
            [
                'shippingMethod' => null,
            ]
        );

        $this->assertInstanceOf(ShippingMethodInterface::class, $option->getShippingMethod());
    }
}

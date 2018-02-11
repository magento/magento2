<?php

namespace Magento\InstantPurchase\Test\Unit\Model;

use Magento\InstantPurchase\Model\InstantPurchaseOption;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Customer\Model\Address;
use Magento\Quote\Api\Data\ShippingMethodInterface;

class InstantPurchaseOptionTest extends TestCase
{
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
        $this->paymentTokenMock = $this->createMock(PaymentTokenInterface::class);
        $this->addressMock = $this->createMock(Address::class);
        $this->shippingMethodMock = $this->createMock(ShippingMethodInterface::class);
        $this->shippingMethodMock->method('getAvailable')
            ->willReturn(true);
    }

    public function testIsAvailable()
    {
        $option = new InstantPurchaseOption(
            $this->paymentTokenMock,
            $this->addressMock,
            $this->addressMock,
            $this->shippingMethodMock
        );

        $this->assertTrue($option->isAvailable());
    }

    public function testIsAvailableFalse()
    {
        $option = new InstantPurchaseOption();
        $this->assertFalse($option->isAvailable());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testGetPaymentTokenException()
    {
        $option = new InstantPurchaseOption();
        $option->getPaymentToken();
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testGetShippingAddressException()
    {
        $option = new InstantPurchaseOption();
        $option->getShippingAddress();
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testGetBillingAddressException()
    {
        $option = new InstantPurchaseOption();
        $option->getBillingAddress();
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testGetShippingMethodException()
    {
        $option = new InstantPurchaseOption();
        $option->getShippingMethod();
    }
}

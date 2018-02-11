<?php

namespace Magento\InstantPurchase\Test\Unit\Model\BillingAddressChoose;

use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\InstantPurchase\Model\BillingAddressChoose\DefaultBillingAddressChooser;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DefaultBillingAddressChooserTest extends TestCase
{
    /**
     * @var DefaultBillingAddressChooser
     */
    private $defaultBillingAddressChooser;

    /**
     * @var Customer|MockObject
     */
    private $customerMock;

    /**
     * @var Address|MockObject
     */
    private $addressMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->defaultBillingAddressChooser = $objectManager->getObject(DefaultBillingAddressChooser::class);
    }

    public function testChoose()
    {
        $this->customerMock->expects($this->once())
            ->method('getDefaultBillingAddress')
            ->willReturn($this->addressMock);

        $this->assertInstanceOf(Address::class, $this->defaultBillingAddressChooser->choose($this->customerMock));
    }

    public function testChooseNoDefault()
    {
        $this->customerMock->expects($this->once())
            ->method('getDefaultBillingAddress')
            ->willReturn(false);

        $this->assertNull($this->defaultBillingAddressChooser->choose($this->customerMock));
    }
}

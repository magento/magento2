<?php
/**
 * Created by PhpStorm.
 * User: jpolak
 * Date: 9/13/17
 * Time: 1:34 PM
 */

namespace Magento\OneTouchOrdering\Test\Unit\Model;

use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Directory\Model\Country;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OneTouchOrdering\Model\CustomerAddressesFormatter;
use PHPUnit\Framework\TestCase;

class CustomerAddressesFormatterTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Customer
     */
    protected $customer;
    /**
     * @var Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $address;
    /**
     * @var CustomerAddressesFormatter
     */
    protected $customerAddresses;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->customer = $this->createMock(Customer::class);
        $this->address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCountryModel', 'getId', 'getName'])
            ->getMock();
        $this->customerAddresses = $objectManager->getObject(CustomerAddressesFormatter::class);
    }

    public function testGetFormattedAddresses()
    {
        $addressId = 123;
        $addressData = [
            'region' => 'Alabama',
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'street' => 'test street',
            'city' => 'test city',
            'postcode' => '12345',
        ];

        $expectedResult = [
            [
                'address' => 'firstname lastname, test street, test city, Alabama 12345, United States',
                'id' => 123
            ]
        ];
        $countryModel = $this->createMock(Country::class);
        $this->customer->expects($this->once())->method('getAddresses')->willReturn([$this->address]);
        $this->address
            ->expects($this->once())
            ->method('getName')
            ->willReturn($addressData['firstname'] . ' ' . $addressData['lastname']);
        $this->address->expects($this->once())->method('getCountryModel')->willReturn($countryModel);
        $this->address->expects($this->once())->method('getId')->willReturn($addressId);
        $this->address->setData($addressData);
        $countryModel->expects($this->once())->method('getName')->willReturn('United States');

        $result = $this->customerAddresses->getFormattedAddresses($this->customer);
        $this->assertSame($result, $expectedResult);
    }
}

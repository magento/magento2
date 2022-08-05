<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InstantPurchase\Test\Unit\Model\Ui;

use Magento\Customer\Model\Address;
use Magento\Directory\Model\Country;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\InstantPurchase\Model\Ui\CustomerAddressesFormatter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerAddressesFormatterTest extends TestCase
{
    /**
     * @var CustomerAddressesFormatter|MockObject
     */
    private $customerAddressesFormatter;

    /**
     * Setup environment for testing
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManagerHelper($this);
        $this->customerAddressesFormatter = $objectManager->getObject(CustomerAddressesFormatter::class);
    }

    /**
     * Test format()
     */
    public function testFormat()
    {
        $addressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['getCity', 'getPostcode'])
            ->onlyMethods(['getName', 'getStreetFull', 'getRegion', 'getCountryModel'])
            ->disableOriginalConstructor()
            ->getMock();
        $countryMock = $this->createMock(Country::class);

        $countryMock->expects($this->any())->method('getName')->willReturn('USA');
        $addressMock->expects($this->any())->method('getName')->willReturn('Address Name');
        $addressMock->expects($this->any())->method('getStreetFull')->willReturn('Address Street Full');
        $addressMock->expects($this->any())->method('getCity')->willReturn('Address City');
        $addressMock->expects($this->any())->method('getRegion')->willReturn('California');
        $addressMock->expects($this->any())->method('getPostcode')->willReturn('12345');
        $addressMock->expects($this->any())->method('getCountryModel')->willReturn($countryMock);

        $this->assertEquals(
            'Address Name, Address Street Full, Address City, California 12345, USA',
            $this->customerAddressesFormatter->format($addressMock)
        );
    }
}

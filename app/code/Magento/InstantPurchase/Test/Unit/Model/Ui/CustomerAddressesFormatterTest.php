<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\InstantPurchase\Test\Unit\Model\Ui;

use Magento\Customer\Model\Address;
use Magento\Directory\Model\Country;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\InstantPurchase\Model\Ui\CustomerAddressesFormatter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerAddressesFormatterTest extends TestCase
{
    use MockCreationTrait;

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
        $addressMock = $this->createPartialMockWithReflection(
            Address::class,
            ['getCity', 'getPostcode', 'getName', 'getStreetFull', 'getRegion', 'getCountryModel']
        );
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

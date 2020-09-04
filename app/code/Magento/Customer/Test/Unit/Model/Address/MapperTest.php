<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Address;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Model\Address\Mapper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MapperTest extends TestCase
{
    /** @var ObjectManager  */
    protected $_objectManager;

    /**
     * @var Mapper
     */
    protected $addressMapper;

    /** @var ExtensibleDataObjectConverter|MockObject */
    protected $extensibleObjectConverter;

    protected function setUp(): void
    {
        $this->extensibleObjectConverter = $this->getMockBuilder(
            ExtensibleDataObjectConverter::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->_objectManager = new ObjectManager($this);
        $this->addressMapper = $this->_objectManager->getObject(
            Mapper::class,
            [
                'extensibleDataObjectConverter' => $this->extensibleObjectConverter
            ]
        );
    }

    public function testToFlatArray()
    {
        $expectedResultWithoutStreet = [
            'id' => 1,
            'default_shipping' => false,
            'default_billing' => true,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'city' => 'Austin',
            'country_id' => 'US',
            'region_id' => 1,
            'region' => 'Texas',
            'region_code' => 'TX'
        ];
        $expectedResultWithStreet = array_merge(
            $expectedResultWithoutStreet,
            [
                'street' => ['7700 W Parmer Ln', 'Austin, TX'],
            ]
        );
        $this->extensibleObjectConverter->expects($this->once())->method('toFlatArray')->willReturn(
            $expectedResultWithoutStreet
        );
        $addressData = $this->createAddressMock();
        $result = $this->addressMapper->toFlatArray($addressData);
        $this->assertEquals($expectedResultWithStreet, $result);
    }

    /**
     * @return AddressInterface|MockObject
     */
    protected function createAddressMock()
    {
        /** @var RegionInterface|MockObject $regionMock */
        $regionMock = $this->getMockForAbstractClass(RegionInterface::class, [], '', false);
        $regionMock->expects($this->any())->method('getRegion')->willReturn('Texas');
        $regionMock->expects($this->any())->method('getRegionId')->willReturn(1);
        $regionMock->expects($this->any())->method('getRegionCode')->willReturn('TX');
        $addressMock = $this->getMockBuilder(AddressInterface::class)
            ->setMethods(
                [
                    'getId',
                    'getDefaultBilling',
                    'getDefaultShipping',
                    'getCity',
                    'getFirstname',
                    'getLastname',
                    'getCountryId',
                    'getRegion',
                    'getStreet'
                ]
            )
            ->getMockForAbstractClass();
        $addressMock->expects($this->any())->method('getId')->willReturn('1');
        $addressMock->expects($this->any())->method('getDefaultBilling')->willReturn(true);
        $addressMock->expects($this->any())->method('getDefaultShipping')->willReturn(false);
        $addressMock->expects($this->any())->method('getCity')->willReturn('Austin');
        $addressMock->expects($this->any())->method('getFirstname')->willReturn('John');
        $addressMock->expects($this->any())->method('getLastname')->willReturn('Doe');
        $addressMock->expects($this->any())->method('getCountryId')->willReturn('US');
        $addressMock->expects($this->any())->method('getRegion')->willReturn($regionMock);
        $addressMock->expects($this->any())->method('getStreet')->willReturn(['7700 W Parmer Ln', 'Austin, TX']);
        return $addressMock;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\Address;

class MapperTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager  */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    protected $addressMapper;

    /** @var \Magento\Framework\Api\ExtensibleDataObjectConverter|\PHPUnit_Framework_MockObject_MockObject */
    protected $extensibleObjectConverter;

    protected function setUp()
    {
        $this->extensibleObjectConverter = $this->getMockBuilder(
            \Magento\Framework\Api\ExtensibleDataObjectConverter::class
        )->disableOriginalConstructor()->getMock();
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->addressMapper = $this->_objectManager->getObject(
            \Magento\Customer\Model\Address\Mapper::class,
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
     * @return \Magento\Customer\Api\Data\AddressInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createAddressMock()
    {
        /** @var \Magento\Customer\Api\Data\RegionInterface|\PHPUnit_Framework_MockObject_MockObject $regionMock */
        $regionMock = $this->getMockForAbstractClass(\Magento\Customer\Api\Data\RegionInterface::class, [], '', false);
        $regionMock->expects($this->any())->method('getRegion')->willReturn('Texas');
        $regionMock->expects($this->any())->method('getRegionId')->willReturn(1);
        $regionMock->expects($this->any())->method('getRegionCode')->willReturn('TX');
        $addressMock = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
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

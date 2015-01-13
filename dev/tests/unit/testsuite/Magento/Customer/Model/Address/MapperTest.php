<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Address;

class MapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\TestFramework\Helper\ObjectManager */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    protected $addressMapper;

    /** @var \Magento\Framework\Api\ExtensibleDataObjectConverter|\PHPUnit_Framework_MockObject_MockObject */
    protected $extensibleObjectConverter;

    protected function setUp()
    {
        $this->extensibleObjectConverter = $this->getMockBuilder('Magento\Framework\Api\ExtensibleDataObjectConverter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->addressMapper = $this->_objectManager->getObject(
            'Magento\Customer\Model\Address\Mapper',
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
        $regionMock = $this->getMockForAbstractClass('Magento\Customer\Api\Data\RegionInterface', [], '', false);
        $regionMock->expects($this->any())->method('getRegion')->willReturn('Texas');
        $regionMock->expects($this->any())->method('getRegionId')->willReturn(1);
        $regionMock->expects($this->any())->method('getRegionCode')->willReturn('TX');
        /** @var \Magento\Customer\Api\Data\AddressInterface|\PHPUnit_Framework_MockObject_MockObject $regionMock */
        $addressMock = $this->getMockForAbstractClass('Magento\Customer\Api\Data\AddressInterface', [], '', false);
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

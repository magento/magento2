<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Service\V1\Address;

use Magento\Checkout\Service\V1\Data\Cart\Address;
use Magento\Checkout\Service\V1\Data\Cart\Address\Region;
use Magento\Framework\Api\AttributeValue;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Converter
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataServiceMock;

    protected function setUp()
    {
        $this->addressBuilderMock = $this->getMock(
            '\Magento\Checkout\Service\V1\Data\Cart\AddressBuilder', [], [], '', false
        );
        $this->metadataServiceMock = $this
            ->getMockBuilder('Magento\Customer\Api\CustomerMetadataInterface')
            ->setMethods(['getCustomAttributesMetadata'])
            ->getMockForAbstractClass();
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Checkout\Service\V1\Address\Converter',
            ['addressBuilder' => $this->addressBuilderMock, 'customerMetadata' => $this->metadataServiceMock]
        );
    }

    public function testConvertModelToDataObject()
    {
        $addressMockMethods = [
            'getCountryId', 'getId', 'getCustomerId', 'getRegion', 'getRegionId', 'getRegionCode',
            'getStreet', 'getCompany', 'getTelephone', 'getFax', 'getPostcode', 'getFirstname', 'getMiddlename',
            'getLastname', 'getPrefix', 'getSuffix', 'getEmail', 'getVatId', 'getCustomField', 'getCity', '__wakeup',
        ];
        $addressMock = $this->getMock('\Magento\Sales\Model\Quote\Address', $addressMockMethods, [], '', false);

        $addressMock->expects($this->atLeastOnce())->method('getCountryId')->will($this->returnValue(1));
        $addressMock->expects($this->atLeastOnce())->method('getId')->will($this->returnValue(2));
        $addressMock->expects($this->atLeastOnce())->method('getCustomerId')->will($this->returnValue(3));
        $addressMock->expects($this->atLeastOnce())->method('getRegion')->will($this->returnValue('Alabama'));
        $addressMock->expects($this->atLeastOnce())->method('getRegionId')->will($this->returnValue(4));
        $addressMock->expects($this->atLeastOnce())->method('getRegionCode')->will($this->returnValue('aa'));
        $addressMock->expects($this->atLeastOnce())->method('getStreet')->will($this->returnValue('street'));
        $addressMock->expects($this->atLeastOnce())->method('getCompany')->will($this->returnValue('company'));
        $addressMock->expects($this->atLeastOnce())->method('getTelephone')->will($this->returnValue('123-123'));
        $addressMock->expects($this->atLeastOnce())->method('getFax')->will($this->returnValue('234-234'));
        $addressMock->expects($this->atLeastOnce())->method('getPostcode')->will($this->returnValue('80010'));
        $addressMock->expects($this->atLeastOnce())->method('getCity')->will($this->returnValue('Town'));
        $addressMock->expects($this->atLeastOnce())->method('getFirstname')->will($this->returnValue('Vasya'));
        $addressMock->expects($this->atLeastOnce())->method('getMiddlename')->will($this->returnValue('Vasya'));
        $addressMock->expects($this->atLeastOnce())->method('getLastname')->will($this->returnValue('Pupkin'));
        $addressMock->expects($this->atLeastOnce())->method('getPrefix')->will($this->returnValue('prefix'));
        $addressMock->expects($this->atLeastOnce())->method('getSuffix')->will($this->returnValue('suffix'));
        $addressMock->expects($this->atLeastOnce())->method('getEmail')->will($this->returnValue('aaa@aaa.com'));
        $addressMock->expects($this->atLeastOnce())->method('getVatId')->will($this->returnValue(5));
        $addressMock->expects($this->atLeastOnce())->method('getCustomField')->will($this->returnValue('custom_value'));

        $testData = [
            Address::KEY_COUNTRY_ID => 1,
            Address::KEY_ID => 2,
            Address::KEY_CUSTOMER_ID => 3,
            Address::KEY_REGION => [
                Region::REGION => 'Alabama',
                Region::REGION_ID => 4,
                Region::REGION_CODE => 'aa',
            ],
            Address::KEY_STREET => 'street',
            Address::KEY_COMPANY => 'company',
            Address::KEY_TELEPHONE => '123-123',
            Address::KEY_FAX => '234-234',
            Address::KEY_POSTCODE => '80010',
            Address::KEY_CITY => 'Town',
            Address::KEY_FIRSTNAME => 'Vasya',
            Address::KEY_LASTNAME => 'Pupkin',
            Address::KEY_MIDDLENAME => 'Vasya',
            Address::KEY_PREFIX => 'prefix',
            Address::KEY_SUFFIX => 'suffix',
            Address::KEY_EMAIL => 'aaa@aaa.com',
            Address::KEY_VAT_ID => 5,
            Address::CUSTOM_ATTRIBUTES_KEY => [['attribute_code' => 'custom_field', 'value' => 'custom_value']],
        ];

        $this->metadataServiceMock
            ->expects($this->any())
            ->method('getCustomAttributesMetadata')
            ->will($this->returnValue([new \Magento\Framework\Object(['attribute_code' => 'custom_field'])]));

        $this->addressBuilderMock->expects($this->once())->method('populateWithArray')->with($testData)->will(
            $this->returnValue($this->addressBuilderMock)
        );
        $this->addressBuilderMock->expects($this->once())->method('create')->will(
            $this->returnValue('Expected value')
        );

        $this->assertEquals('Expected value', $this->model->convertModelToDataObject($addressMock));
    }

    public function testConvertDataObjectToModel()
    {
        $dataObjectMock = $this->getMock('Magento\Checkout\Service\V1\Data\Cart\Address', [], [], '', false);
        $methods = ['setData', 'setStreet', 'setRegionId', 'setRegion', '__wakeUp'];
        $addressMock = $this->getMock('Magento\Sales\Model\Quote\Address', $methods, [], '', false);
        $attributeValueMock = $this->getMock('\Magento\Framework\Api\AttributeValue', [], [], '', false);
        $attributeValueMock->expects($this->once())->method('getAttributeCode')->will($this->returnValue('value_code'));
        $attributeValueMock->expects($this->once())->method('getValue')->will($this->returnValue('value'));

        $addressData = [
            'some_code' => 'some_value',
        ];
        $regionMock = $this->getMock('Magento\Checkout\Service\V1\Data\Cart\Address\Region', [], [], '', false);

        $dataObjectMock->expects($this->once())->method('__toArray')->will($this->returnValue($addressData));
        $valueMap = [
            [$addressData, null],
            ['attribute_value', 'value'],
        ];
        $addressMock->expects($this->any())->method('setData')->will($this->returnValueMap($valueMap));
        $dataObjectMock
            ->expects($this->once())
            ->method('getCustomAttributes')
            ->will($this->returnValue([$attributeValueMock]));
        $dataObjectMock->expects($this->once())->method('getStreet')->will($this->returnValue('street'));
        $addressMock->expects($this->once())->method('setStreet')->with('street');
        $dataObjectMock->expects($this->any())->method('getRegion')->will($this->returnValue($regionMock));
        $regionMock->expects($this->once())->method('getRegionId')->will($this->returnValue('regionId'));
        $regionMock->expects($this->once())->method('getRegion')->will($this->returnValue('region'));
        $addressMock->expects($this->once())->method('setRegionId')->with('regionId');
        $addressMock->expects($this->once())->method('setRegion')->with('region');
        $this->model->convertDataObjectToModel($dataObjectMock, $addressMock);
    }
}

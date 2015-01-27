<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api;

use \Magento\Framework\Api\ExtensibleDataInterface;
use \Magento\Framework\Api\AttributeInterface;

class DataObjectHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Api\ObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectFactoryMock;

    /**
     * @var \Magento\Framework\Reflection\TypeProcessor
     */
    protected $typeProcessor;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectProcessorMock;

    /**
     * @var \Magento\Framework\Api\AttributeValueFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeValueFactoryMock;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->objectFactoryMock = $this->getMockBuilder('\Magento\Framework\Api\ObjectFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectProcessorMock = $this->getMockBuilder('\Magento\Framework\Reflection\DataObjectProcessor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeValueFactoryMock = $this->getMockBuilder('\Magento\Framework\Api\AttributeValueFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeProcessor = $this->objectManager->getObject('\Magento\Framework\Reflection\TypeProcessor');
        $this->dataObjectHelper = $this->objectManager->getObject(
            'Magento\Framework\Api\DataObjectHelper',
            [
                'objectFactory' => $this->objectFactoryMock,
                'typeProcessor' => $this->typeProcessor,
                'objectProcessor' => $this->objectProcessorMock,
            ]
        );
    }

    public function testPopulateWithArrayWithSimpleAttributes()
    {
        $id = 5;
        $countryId = 15;
        $street = ["7700 W Parmer Lane", "second line"];
        $isDefaultShipping = true;

        $regionId = 7;
        $region = "TX";

        /** @var \Magento\Customer\Model\Data\Address $addressDataObject */
        $addressDataObject = $this->objectManager->getObject(
            'Magento\Customer\Model\Data\Address',
            [
                'dataObjectHelper' => $this->dataObjectHelper,
            ]
        );

        /** @var \Magento\Customer\Model\Data\Region $regionDataObject */
        $regionDataObject = $this->objectManager->getObject(
            'Magento\Customer\Model\Data\Region',
            [
                'dataObjectHelper' => $this->dataObjectHelper,
            ]
        );
        $data = [
            'id' => $id,
            'country_id' => $countryId,
            'street' => $street,
            'default_shipping' => $isDefaultShipping,
            'region' => [
                'region_id' => $regionId,
                'region' => $region,
            ],
        ];

        $this->objectProcessorMock->expects($this->at(0))
            ->method('getMethodReturnType')
            ->with('Magento\Customer\Model\Data\Address', 'getStreet')
            ->willReturn('string[]');
        $this->objectProcessorMock->expects($this->at(1))
            ->method('getMethodReturnType')
            ->with('Magento\Customer\Model\Data\Address', 'getRegion')
            ->willReturn('\Magento\Customer\Api\Data\RegionInterface');
        $this->objectFactoryMock->expects($this->once())
            ->method('create')
            ->with('\Magento\Customer\Api\Data\RegionInterface', [])
            ->willReturn($regionDataObject);

        $this->dataObjectHelper->populateWithArray($addressDataObject, $data);

        $this->assertEquals($id, $addressDataObject->getId());
        $this->assertEquals($countryId, $addressDataObject->getCountryId());
        $this->assertEquals($street, $addressDataObject->getStreet());
        $this->assertEquals($isDefaultShipping, $addressDataObject->isDefaultShipping());
        $this->assertEquals($region, $addressDataObject->getRegion()->getRegion());
        $this->assertEquals($regionId, $addressDataObject->getRegion()->getRegionId());
    }

    public function testPopulateWithArrayWithCustomAttribute()
    {
        $id = 5;

        $customAttributeCode = 'custom_attribute_code_1';
        $customAttributeValue = 'custom_attribute_value_1';

        /** @var \Magento\Customer\Model\Data\Address $addressDataObject */
        $addressDataObject = $this->objectManager->getObject(
            'Magento\Customer\Model\Data\Address',
            [
                'dataObjectHelper' => $this->dataObjectHelper,
                'attributeValueFactory' => $this->attributeValueFactoryMock,
            ]
        );

        $data = [
            'id' => $id,
            $customAttributeCode => $customAttributeValue,
        ];

        $attributeMetaDataMock = $this->getMockBuilder('\Magento\Customer\Api\Data\AttributeMetadataInterface')
            ->getMock();
        $attributeMetaDataMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($customAttributeCode);
        $metadataServiceMock = $this->getMockBuilder('Magento\Customer\Model\Metadata\AddressMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $metadataServiceMock->expects($this->once())
            ->method('getCustomAttributesMetadata')
            ->with('Magento\Customer\Model\Data\Address')
            ->willReturn(
                [$attributeMetaDataMock]
            );

        $this->objectFactoryMock->expects($this->once())
            ->method('get')
            ->with('\Magento\Customer\Api\AddressMetadataInterface')
            ->willReturn($metadataServiceMock);

        $customAttribute = $this->objectManager->getObject('Magento\Framework\Api\AttributeValue');
        $this->attributeValueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customAttribute);
        $this->dataObjectHelper->populateWithArray($addressDataObject, $data);

        $this->assertEquals($id, $addressDataObject->getId());
        $this->assertEquals(
            $customAttributeValue,
            $addressDataObject->getCustomAttribute($customAttributeCode)->getValue());
        $this->assertEquals(
            $customAttributeCode,
            $addressDataObject->getCustomAttribute($customAttributeCode)->getAttributeCode());
    }

    public function testPopulateWithArrayWithCustomAttributes()
    {
        $id = 5;

        $customAttributeCode = 'custom_attribute_code_1';
        $customAttributeValue = 'custom_attribute_value_1';

        /** @var \Magento\Customer\Model\Data\Address $addressDataObject */
        $addressDataObject = $this->objectManager->getObject(
            'Magento\Customer\Model\Data\Address',
            [
                'dataObjectHelper' => $this->dataObjectHelper,
                'attributeValueFactory' => $this->attributeValueFactoryMock,
            ]
        );

        $data = [
            'id' => $id,
            ExtensibleDataInterface::CUSTOM_ATTRIBUTES => [
                [
                    AttributeInterface::ATTRIBUTE_CODE => $customAttributeCode,
                    AttributeInterface::VALUE => $customAttributeValue,
                ],
            ],
        ];

        $attributeMetaDataMock = $this->getMockBuilder('\Magento\Customer\Api\Data\AttributeMetadataInterface')
            ->getMock();
        $attributeMetaDataMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($customAttributeCode);
        $metadataServiceMock = $this->getMockBuilder('Magento\Customer\Model\Metadata\AddressMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $metadataServiceMock->expects($this->once())
            ->method('getCustomAttributesMetadata')
            ->with('Magento\Customer\Model\Data\Address')
            ->willReturn(
                [$attributeMetaDataMock]
            );

        $this->objectFactoryMock->expects($this->once())
            ->method('get')
            ->with('\Magento\Customer\Api\AddressMetadataInterface')
            ->willReturn($metadataServiceMock);

        $customAttribute = $this->objectManager->getObject('Magento\Framework\Api\AttributeValue');
        $this->attributeValueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customAttribute);
        $this->dataObjectHelper->populateWithArray($addressDataObject, $data);

        $this->assertEquals($id, $addressDataObject->getId());
        $this->assertEquals(
            $customAttributeValue,
            $addressDataObject->getCustomAttribute($customAttributeCode)->getValue());
        $this->assertEquals(
            $customAttributeCode,
            $addressDataObject->getCustomAttribute($customAttributeCode)->getAttributeCode());
    }
}

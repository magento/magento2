<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\Test\Unit;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Model\Data\Address;
use Magento\Customer\Model\Data\Region;
use Magento\Customer\Model\Metadata\AddressMetadata;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessor;
use Magento\Framework\Api\ObjectFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataObjectHelperTest extends TestCase
{
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ObjectFactory|MockObject
     */
    protected $objectFactoryMock;

    /**
     * @var TypeProcessor
     */
    protected $typeProcessor;

    /**
     * @var DataObjectProcessor|MockObject
     */
    protected $objectProcessorMock;

    /**
     * @var AttributeValueFactory|MockObject
     */
    protected $attributeValueFactoryMock;

    /**
     * @var MethodsMap|MockObject
     */
    protected $methodsMapProcessor;

    /**
     * @var JoinProcessor|MockObject
     */
    protected $joinProcessorMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->objectFactoryMock = $this->getMockBuilder(ObjectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectProcessorMock = $this->getMockBuilder(DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->methodsMapProcessor = $this->getMockBuilder(MethodsMap::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeValueFactoryMock = $this->getMockBuilder(AttributeValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->joinProcessorMock = $this->getMockBuilder(JoinProcessor::class)
            ->setMethods(['extractExtensionAttributes'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->joinProcessorMock->expects($this->any())
            ->method('extractExtensionAttributes')
            ->willReturnArgument(1);
        $this->typeProcessor = $this->objectManager->getObject(TypeProcessor::class);

        $this->dataObjectHelper = $this->objectManager->getObject(
            DataObjectHelper::class,
            [
                'objectFactory' => $this->objectFactoryMock,
                'typeProcessor' => $this->typeProcessor,
                'objectProcessor' => $this->objectProcessorMock,
                'methodsMapProcessor' => $this->methodsMapProcessor,
                'joinProcessor' => $this->joinProcessorMock
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

        /** @var Address $addressDataObject */
        $addressDataObject = $this->objectManager->getObject(
            Address::class,
            [
                'dataObjectHelper' => $this->dataObjectHelper,
            ]
        );

        /** @var Region $regionDataObject */
        $regionDataObject = $this->objectManager->getObject(
            Region::class,
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

        $this->methodsMapProcessor->expects($this->at(0))
            ->method('getMethodReturnType')
            ->with(AddressInterface::class, 'getStreet')
            ->willReturn('string[]');
        $this->methodsMapProcessor->expects($this->at(1))
            ->method('getMethodReturnType')
            ->with(AddressInterface::class, 'getRegion')
            ->willReturn(RegionInterface::class);
        $this->objectFactoryMock->expects($this->once())
            ->method('create')
            ->with(RegionInterface::class, [])
            ->willReturn($regionDataObject);

        $this->dataObjectHelper->populateWithArray(
            $addressDataObject,
            $data,
            AddressInterface::class
        );

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

        $attributeMetaDataMock = $this->getMockBuilder(AttributeMetadataInterface::class)
            ->getMock();
        $attributeMetaDataMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($customAttributeCode);
        $metadataServiceMock = $this->getMockBuilder(AddressMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataServiceMock->expects($this->once())
            ->method('getCustomAttributesMetadata')
            ->with(Address::class)
            ->willReturn(
                [$attributeMetaDataMock]
            );

        /** @var Address $addressDataObject */
        $addressDataObject = $this->objectManager->getObject(
            Address::class,
            [
                'dataObjectHelper' => $this->dataObjectHelper,
                'metadataService' => $metadataServiceMock,
                'attributeValueFactory' => $this->attributeValueFactoryMock,
            ]
        );

        $data = [
            'id' => $id,
            $customAttributeCode => $customAttributeValue,
        ];

        $customAttribute = $this->objectManager->getObject(AttributeValue::class);
        $this->attributeValueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customAttribute);
        $this->dataObjectHelper->populateWithArray(
            $addressDataObject,
            $data,
            AddressInterface::class
        );

        $this->assertEquals($id, $addressDataObject->getId());
        $this->assertEquals(
            $customAttributeValue,
            $addressDataObject->getCustomAttribute($customAttributeCode)->getValue()
        );
        $this->assertEquals(
            $customAttributeCode,
            $addressDataObject->getCustomAttribute($customAttributeCode)->getAttributeCode()
        );
    }

    public function testPopulateWithArrayWithCustomAttributes()
    {
        $id = 5;

        $customAttributeCode = 'custom_attribute_code_1';
        $customAttributeValue = 'custom_attribute_value_1';

        $attributeMetaDataMock = $this->getMockBuilder(AttributeMetadataInterface::class)
            ->getMock();
        $attributeMetaDataMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($customAttributeCode);
        $metadataServiceMock = $this->getMockBuilder(AddressMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataServiceMock->expects($this->once())
            ->method('getCustomAttributesMetadata')
            ->with(Address::class)
            ->willReturn(
                [$attributeMetaDataMock]
            );

        /** @var Address $addressDataObject */
        $addressDataObject = $this->objectManager->getObject(
            Address::class,
            [
                'dataObjectHelper' => $this->dataObjectHelper,
                'metadataService' => $metadataServiceMock,
                'attributeValueFactory' => $this->attributeValueFactoryMock,
            ]
        );

        $data = [
            'id' => $id,
            CustomAttributesDataInterface::CUSTOM_ATTRIBUTES => [
                [
                    AttributeInterface::ATTRIBUTE_CODE => $customAttributeCode,
                    AttributeInterface::VALUE => $customAttributeValue,
                ],
            ],
        ];

        $customAttribute = $this->objectManager->getObject(AttributeValue::class);
        $this->attributeValueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customAttribute);
        $this->dataObjectHelper->populateWithArray(
            $addressDataObject,
            $data,
            AddressInterface::class
        );

        $this->assertEquals($id, $addressDataObject->getId());
        $this->assertEquals(
            $customAttributeValue,
            $addressDataObject->getCustomAttribute($customAttributeCode)->getValue()
        );
        $this->assertEquals(
            $customAttributeCode,
            $addressDataObject->getCustomAttribute($customAttributeCode)->getAttributeCode()
        );
    }

    /**
     * @param array $data1
     * @param array $data2
     * @dataProvider dataProviderForTestMergeDataObjects
     */
    public function testMergeDataObjects($data1, $data2)
    {
        /** @var Address $addressDataObject */
        $firstAddressDataObject = $this->objectManager->getObject(
            Address::class,
            [
                'dataObjectHelper' => $this->dataObjectHelper,
            ]
        );

        /** @var Region $regionDataObject */
        $firstRegionDataObject = $this->objectManager->getObject(
            Region::class,
            [
                'dataObjectHelper' => $this->dataObjectHelper,
            ]
        );

        $firstRegionDataObject->setRegionId($data1['region']['region_id']);
        $firstRegionDataObject->setRegion($data1['region']['region']);
        if (isset($data1['id'])) {
            $firstAddressDataObject->setId($data1['id']);
        }
        if (isset($data1['country_id'])) {
            $firstAddressDataObject->setCountryId($data1['country_id']);
        }
        $firstAddressDataObject->setStreet($data1['street']);
        $firstAddressDataObject->setIsDefaultShipping($data1['default_shipping']);
        $firstAddressDataObject->setRegion($firstRegionDataObject);

        $secondAddressDataObject = $this->objectManager->getObject(
            Address::class,
            [
                'dataObjectHelper' => $this->dataObjectHelper,
            ]
        );

        /** @var Region $regionDataObject */
        $secondRegionDataObject = $this->objectManager->getObject(
            Region::class,
            [
                'dataObjectHelper' => $this->dataObjectHelper,
            ]
        );

        $secondRegionDataObject->setRegionId($data2['region']['region_id']);
        $secondRegionDataObject->setRegion($data2['region']['region']);
        if (isset($data2['id'])) {
            $secondAddressDataObject->setId($data2['id']);
        }
        if (isset($data2['country_id'])) {
            $secondAddressDataObject->setCountryId($data2['country_id']);
        }
        $secondAddressDataObject->setStreet($data2['street']);
        $secondAddressDataObject->setIsDefaultShipping($data2['default_shipping']);
        $secondAddressDataObject->setRegion($secondRegionDataObject);

        $this->objectProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($secondAddressDataObject, get_class($firstAddressDataObject))
            ->willReturn($data2);
        $this->methodsMapProcessor->expects($this->at(0))
            ->method('getMethodReturnType')
            ->with(Address::class, 'getStreet')
            ->willReturn('string[]');
        $this->methodsMapProcessor->expects($this->at(1))
            ->method('getMethodReturnType')
            ->with(Address::class, 'getRegion')
            ->willReturn(RegionInterface::class);
        $this->objectFactoryMock->expects($this->once())
            ->method('create')
            ->with(RegionInterface::class, [])
            ->willReturn($secondRegionDataObject);

        $this->dataObjectHelper->mergeDataObjects(
            get_class($firstAddressDataObject),
            $firstAddressDataObject,
            $secondAddressDataObject
        );

        $this->assertSame($firstAddressDataObject->getId(), $secondAddressDataObject->getId());
        $this->assertSame($firstAddressDataObject->getCountryId(), $secondAddressDataObject->getCountryId());
        $this->assertSame($firstAddressDataObject->getStreet(), $secondAddressDataObject->getStreet());
        $this->assertSame($firstAddressDataObject->isDefaultShipping(), $secondAddressDataObject->isDefaultShipping());
        $this->assertSame($firstAddressDataObject->getRegion(), $secondAddressDataObject->getRegion());
    }

    /**
     * @return array
     */
    public function dataProviderForTestMergeDataObjects()
    {
        return [
            [
                [
                    'id' => '1',
                    'country_id' => '1',
                    'street' => ["7701 W Parmer Lane", "Second Line"],
                    'default_shipping' => true,
                    'region' => [
                        'region_id' => '1',
                        'region' => 'TX',
                    ]
                ],
                [
                    'id' => '2',
                    'country_id' => '2',
                    'street' => ["7702 W Parmer Lane", "Second Line"],
                    'default_shipping' => false,
                    'region' => [
                        'region_id' => '2',
                        'region' => 'TX',
                    ]
                ]
            ],
            [
                [
                    'street' => ["7701 W Parmer Lane", "Second Line"],
                    'default_shipping' => true,
                    'region' => [
                        'region_id' => '1',
                        'region' => 'TX',
                    ]
                ],
                [
                    'id' => '2',
                    'country_id' => '2',
                    'street' => ["7702 W Parmer Lane", "Second Line"],
                    'default_shipping' => false,
                    'region' => [
                        'region_id' => '2',
                        'region' => 'TX',
                    ]
                ]
            ]
        ];
    }
}

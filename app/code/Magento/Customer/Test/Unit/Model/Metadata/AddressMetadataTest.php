<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Metadata;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\AttributeMetadataConverter;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Customer\Model\Metadata\AddressMetadata;
use Magento\Customer\Model\ResourceModel\Form\Attribute\Collection;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

class AddressMetadataTest extends \PHPUnit_Framework_TestCase
{
    /** @var AddressMetadata */
    protected $model;

    /** @var AttributeMetadataConverter|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeConverterMock;

    /** @var AttributeMetadataDataProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeProviderMock;

    protected function setUp()
    {
        $this->attributeConverterMock = $this->getMockBuilder('Magento\Customer\Model\AttributeMetadataConverter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeProviderMock = $this->getMockBuilder('Magento\Customer\Model\AttributeMetadataDataProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new AddressMetadata(
            $this->attributeConverterMock,
            $this->attributeProviderMock
        );
    }

    public function testGetAttributes()
    {
        $formCode = 'formcode';
        $attributeCode = 'attr';

        /** @var Attribute|\PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder('Magento\Customer\Model\Attribute')
            ->disableOriginalConstructor()
            ->getMock();
        $attributes = [$attributeMock];

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder('Magento\Customer\Model\ResourceModel\Form\Attribute\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeProviderMock->expects($this->once())
            ->method('loadAttributesCollection')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $formCode)
            ->willReturn($collectionMock);

        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($attributes));

        $attributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $metadataMock */
        $metadataMock = $this->getMockBuilder('Magento\Customer\Api\Data\AttributeMetadataInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $result = [$attributeCode => $metadataMock];

        $this->attributeConverterMock->expects($this->once())
            ->method('createMetadataAttribute')
            ->with($attributeMock)
            ->willReturn($metadataMock);

        $this->assertEquals($result, $this->model->getAttributes($formCode));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with formCode = formcode
     */
    public function testGetAttributesWithException()
    {
        $formCode = 'formcode';
        $attributes = [];

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder('Magento\Customer\Model\ResourceModel\Form\Attribute\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeProviderMock->expects($this->once())
            ->method('loadAttributesCollection')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $formCode)
            ->willReturn($collectionMock);

        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($attributes));

        $this->model->getAttributes($formCode);
    }

    public function testGetAttributeMetadata()
    {
        $attributeCode = 'attr';
        $attributeId = 12;

        /** @var AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();

        $this->attributeProviderMock->expects($this->once())
            ->method('getAttribute')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $attributeCode)
            ->willReturn($attributeMock);

        $attributeMock->expects($this->once())
            ->method('getId')
            ->willReturn($attributeId);

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $metadataMock */
        $metadataMock = $this->getMockBuilder('Magento\Customer\Api\Data\AttributeMetadataInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeConverterMock->expects($this->once())
            ->method('createMetadataAttribute')
            ->with($attributeMock)
            ->willReturn($metadataMock);

        $this->assertEquals($metadataMock, $this->model->getAttributeMetadata($attributeCode));
    }

    public function testGetAttributeMetadataWithCodeId()
    {
        $attributeCode = 'id';

        /** @var AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->attributeProviderMock->expects($this->once())
            ->method('getAttribute')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $attributeCode)
            ->willReturn($attributeMock);

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $metadataMock */
        $metadataMock = $this->getMockBuilder('Magento\Customer\Api\Data\AttributeMetadataInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeConverterMock->expects($this->once())
            ->method('createMetadataAttribute')
            ->with($attributeMock)
            ->willReturn($metadataMock);

        $this->assertEquals($metadataMock, $this->model->getAttributeMetadata($attributeCode));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with entityType = customer_address, attributeCode = id
     */
    public function testGetAttributeMetadataWithoutAttribute()
    {
        $attributeCode = 'id';

        $this->attributeProviderMock->expects($this->once())
            ->method('getAttribute')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $attributeCode)
            ->willReturn(null);

        $this->model->getAttributeMetadata($attributeCode);
    }

    public function testGetAllAttributesMetadata()
    {
        $attributeCode = 'id';
        $attributeCodes = [$attributeCode];

        $this->attributeProviderMock->expects($this->once())
            ->method('getAllAttributeCodes')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS)
            ->willReturn($attributeCodes);

        /** @var AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->attributeProviderMock->expects($this->once())
            ->method('getAttribute')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $attributeCode)
            ->willReturn($attributeMock);

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $metadataMock */
        $metadataMock = $this->getMockBuilder('Magento\Customer\Api\Data\AttributeMetadataInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $result = [$metadataMock];

        $this->attributeConverterMock->expects($this->once())
            ->method('createMetadataAttribute')
            ->with($attributeMock)
            ->willReturn($metadataMock);

        $this->assertEquals($result, $this->model->getAllAttributesMetadata());
    }

    public function testGetAllAttributesMetadataWithoutEntity()
    {
        $attributeCode = 'id';
        $attributeCodes = [$attributeCode];

        $this->attributeProviderMock->expects($this->once())
            ->method('getAllAttributeCodes')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS)
            ->willReturn($attributeCodes);

        $this->attributeProviderMock->expects($this->once())
            ->method('getAttribute')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $attributeCode)
            ->willReturn(null);

        $result = [];

        $this->assertEquals($result, $this->model->getAllAttributesMetadata());
    }

    public function testGetCustomAttributesMetadata()
    {
        $attributeCode = 'attr';
        $attributeId = 12;
        $attributeCodes = [$attributeCode];

        $this->attributeProviderMock->expects($this->once())
            ->method('getAllAttributeCodes')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS)
            ->willReturn($attributeCodes);

        /** @var AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();

        $this->attributeProviderMock->expects($this->once())
            ->method('getAttribute')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $attributeCode)
            ->willReturn($attributeMock);

        $attributeMock->expects($this->once())
            ->method('getId')
            ->willReturn($attributeId);

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $metadataMock */
        $metadataMock = $this->getMockBuilder('Magento\Customer\Api\Data\AttributeMetadataInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $result = [$metadataMock];

        $this->attributeConverterMock->expects($this->once())
            ->method('createMetadataAttribute')
            ->with($attributeMock)
            ->willReturn($metadataMock);

        $metadataMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $metadataMock->expects($this->once())
            ->method('isSystem')
            ->willReturn(false);

        $this->assertEquals($result, $this->model->getCustomAttributesMetadata());
    }

    public function testGetCustomAttributesMetadataWithSystemAttribute()
    {
        $attributeCode = 'attr';
        $attributeId = 12;
        $attributeCodes = [$attributeCode];

        $this->attributeProviderMock->expects($this->once())
            ->method('getAllAttributeCodes')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS)
            ->willReturn($attributeCodes);

        /** @var AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();

        $this->attributeProviderMock->expects($this->once())
            ->method('getAttribute')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $attributeCode)
            ->willReturn($attributeMock);

        $attributeMock->expects($this->once())
            ->method('getId')
            ->willReturn($attributeId);

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $metadataMock */
        $metadataMock = $this->getMockBuilder('Magento\Customer\Api\Data\AttributeMetadataInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $result = [];

        $this->attributeConverterMock->expects($this->once())
            ->method('createMetadataAttribute')
            ->with($attributeMock)
            ->willReturn($metadataMock);

        $metadataMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $metadataMock->expects($this->once())
            ->method('isSystem')
            ->willReturn(true);

        $this->assertEquals($result, $this->model->getCustomAttributesMetadata());
    }

    public function testGetCustomAttributesMetadataWithoutAttributes()
    {
        $attributeCode = 'id';
        $attributeCodes = [$attributeCode];

        $this->attributeProviderMock->expects($this->once())
            ->method('getAllAttributeCodes')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS)
            ->willReturn($attributeCodes);

        /** @var AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->attributeProviderMock->expects($this->once())
            ->method('getAttribute')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $attributeCode)
            ->willReturn($attributeMock);

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $metadataMock */
        $metadataMock = $this->getMockBuilder('Magento\Customer\Api\Data\AttributeMetadataInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $result = [];

        $this->attributeConverterMock->expects($this->once())
            ->method('createMetadataAttribute')
            ->with($attributeMock)
            ->willReturn($metadataMock);

        $metadataMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        $this->assertEquals($result, $this->model->getCustomAttributesMetadata());
    }
}

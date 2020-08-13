<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Metadata;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\AttributeMetadataConverter;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Customer\Model\Metadata\AddressMetadata;
use Magento\Customer\Model\ResourceModel\Form\Attribute\Collection;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddressMetadataTest extends TestCase
{
    /** @var AddressMetadata */
    protected $model;

    /** @var AttributeMetadataConverter|MockObject */
    protected $attributeConverterMock;

    /** @var AttributeMetadataDataProvider|MockObject */
    protected $attributeProviderMock;

    protected function setUp(): void
    {
        $this->attributeConverterMock = $this->getMockBuilder(AttributeMetadataConverter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeProviderMock = $this->getMockBuilder(
            AttributeMetadataDataProvider::class
        )
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

        /** @var Attribute|MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributes = [$attributeMock];

        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(Collection::class)
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

        /** @var AttributeMetadataInterface|MockObject $metadataMock */
        $metadataMock = $this->getMockBuilder(AttributeMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $result = [$attributeCode => $metadataMock];

        $this->attributeConverterMock->expects($this->once())
            ->method('createMetadataAttribute')
            ->with($attributeMock)
            ->willReturn($metadataMock);

        $this->assertEquals($result, $this->model->getAttributes($formCode));
    }

    public function testGetAttributesWithException()
    {
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('No such entity with formCode = formcode');

        $formCode = 'formcode';
        $attributes = [];

        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(Collection::class)
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

        /** @var AbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
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

        /** @var AttributeMetadataInterface|MockObject $metadataMock */
        $metadataMock = $this->getMockBuilder(AttributeMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->attributeConverterMock->expects($this->once())
            ->method('createMetadataAttribute')
            ->with($attributeMock)
            ->willReturn($metadataMock);

        $this->assertEquals($metadataMock, $this->model->getAttributeMetadata($attributeCode));
    }

    public function testGetAttributeMetadataWithCodeId()
    {
        $attributeCode = 'id';

        /** @var AbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->attributeProviderMock->expects($this->once())
            ->method('getAttribute')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $attributeCode)
            ->willReturn($attributeMock);

        /** @var AttributeMetadataInterface|MockObject $metadataMock */
        $metadataMock = $this->getMockBuilder(AttributeMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->attributeConverterMock->expects($this->once())
            ->method('createMetadataAttribute')
            ->with($attributeMock)
            ->willReturn($metadataMock);

        $this->assertEquals($metadataMock, $this->model->getAttributeMetadata($attributeCode));
    }

    public function testGetAttributeMetadataWithoutAttribute()
    {
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('No such entity with entityType = customer_address, attributeCode = id');

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

        /** @var AbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->attributeProviderMock->expects($this->once())
            ->method('getAttribute')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $attributeCode)
            ->willReturn($attributeMock);

        /** @var AttributeMetadataInterface|MockObject $metadataMock */
        $metadataMock = $this->getMockBuilder(AttributeMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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

        /** @var AbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
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

        /** @var AttributeMetadataInterface|MockObject $metadataMock */
        $metadataMock = $this->getMockBuilder(AttributeMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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

        /** @var AbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
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

        /** @var AttributeMetadataInterface|MockObject $metadataMock */
        $metadataMock = $this->getMockBuilder(AttributeMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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

        /** @var AbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->attributeProviderMock->expects($this->once())
            ->method('getAttribute')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $attributeCode)
            ->willReturn($attributeMock);

        /** @var AttributeMetadataInterface|MockObject $metadataMock */
        $metadataMock = $this->getMockBuilder(AttributeMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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

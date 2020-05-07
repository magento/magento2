<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Metadata;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Customer\Model\Metadata\AttributeResolver;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeResolverTest extends TestCase
{
    /** @var AttributeResolver */
    protected $model;

    /** @var AttributeMetadataDataProvider|MockObject */
    protected $metadataDataProviderMock;

    protected function setUp(): void
    {
        $this->metadataDataProviderMock = $this->getMockBuilder(
            AttributeMetadataDataProvider::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->model = new AttributeResolver(
            $this->metadataDataProviderMock
        );
    }

    public function testGetModelByAttribute()
    {
        $entityType = 'type';
        $attributeCode = 'code';

        /** @var AttributeMetadataInterface|MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(AttributeMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        /** @var Attribute|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->metadataDataProviderMock->expects($this->once())
            ->method('getAttribute')
            ->with($entityType, $attributeCode)
            ->willReturn($modelMock);

        $this->assertEquals($modelMock, $this->model->getModelByAttribute($entityType, $attributeMock));
    }

    public function testGetModelByAttributeWithoutModel()
    {
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('No such entity with entityType = type, attributeCode = code');

        $entityType = 'type';
        $attributeCode = 'code';

        /** @var AttributeMetadataInterface|MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(AttributeMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attributeMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        $this->metadataDataProviderMock->expects($this->once())
            ->method('getAttribute')
            ->with($entityType, $attributeCode)
            ->willReturn(false);

        $this->model->getModelByAttribute($entityType, $attributeMock);
    }
}

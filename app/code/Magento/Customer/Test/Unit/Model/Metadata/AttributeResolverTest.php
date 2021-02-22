<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Metadata;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Customer\Model\Metadata\AttributeResolver;

class AttributeResolverTest extends \PHPUnit\Framework\TestCase
{
    /** @var AttributeResolver */
    protected $model;

    /** @var AttributeMetadataDataProvider|\PHPUnit\Framework\MockObject\MockObject */
    protected $metadataDataProviderMock;

    protected function setUp(): void
    {
        $this->metadataDataProviderMock = $this->getMockBuilder(
            \Magento\Customer\Model\AttributeMetadataDataProvider::class
        )->disableOriginalConstructor()->getMock();

        $this->model = new AttributeResolver(
            $this->metadataDataProviderMock
        );
    }

    public function testGetModelByAttribute()
    {
        $entityType = 'type';
        $attributeCode = 'code';

        /** @var AttributeMetadataInterface|\PHPUnit\Framework\MockObject\MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(\Magento\Customer\Api\Data\AttributeMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        /** @var Attribute|\PHPUnit\Framework\MockObject\MockObject $modelMock */
        $modelMock = $this->getMockBuilder(\Magento\Customer\Model\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->metadataDataProviderMock->expects($this->once())
            ->method('getAttribute')
            ->with($entityType, $attributeCode)
            ->willReturn($modelMock);

        $this->assertEquals($modelMock, $this->model->getModelByAttribute($entityType, $attributeMock));
    }

    /**
     */
    public function testGetModelByAttributeWithoutModel()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->expectExceptionMessage('No such entity with entityType = type, attributeCode = code');

        $entityType = 'type';
        $attributeCode = 'code';

        /** @var AttributeMetadataInterface|\PHPUnit\Framework\MockObject\MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(\Magento\Customer\Api\Data\AttributeMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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

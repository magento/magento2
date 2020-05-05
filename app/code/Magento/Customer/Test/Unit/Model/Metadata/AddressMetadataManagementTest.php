<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Metadata;

use Magento\Customer\Api\AddressMetadataManagementInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\Metadata\AddressMetadataManagement;
use Magento\Customer\Model\Metadata\AttributeResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddressMetadataManagementTest extends TestCase
{
    /** @var AddressMetadataManagement */
    protected $model;

    /** @var AttributeResolver|MockObject */
    protected $attributeResolverMock;

    protected function setUp(): void
    {
        $this->attributeResolverMock = $this->getMockBuilder(AttributeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new AddressMetadataManagement(
            $this->attributeResolverMock
        );
    }

    public function testCanBeSearchableInGrid()
    {
        /** @var AttributeMetadataInterface|MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(AttributeMetadataInterface::class)
            ->getMockForAbstractClass();

        /** @var Attribute|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeResolverMock->expects($this->once())
            ->method('getModelByAttribute')
            ->with(AddressMetadataManagementInterface::ENTITY_TYPE_ADDRESS, $attributeMock)
            ->willReturn($modelMock);

        $modelMock->expects($this->once())
            ->method('canBeSearchableInGrid')
            ->willReturn(true);

        $this->assertTrue($this->model->canBeSearchableInGrid($attributeMock));
    }

    public function testCanBeFilterableInGrid()
    {
        /** @var AttributeMetadataInterface|MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(AttributeMetadataInterface::class)
            ->getMockForAbstractClass();

        /** @var Attribute|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeResolverMock->expects($this->once())
            ->method('getModelByAttribute')
            ->with(AddressMetadataManagementInterface::ENTITY_TYPE_ADDRESS, $attributeMock)
            ->willReturn($modelMock);

        $modelMock->expects($this->once())
            ->method('canBeFilterableInGrid')
            ->willReturn(true);

        $this->assertTrue($this->model->canBeFilterableInGrid($attributeMock));
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Metadata;

use Magento\Customer\Model\Attribute;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\Metadata\CustomerMetadataManagement;

class CustomerMetadataManagementTest extends \PHPUnit_Framework_TestCase
{
    /** @var CustomerMetadataManagement */
    protected $model;

    /** @var \Magento\Customer\Model\Metadata\AttributeResolver|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeResolverMock;

    protected function setUp()
    {
        $this->attributeResolverMock = $this->getMockBuilder(\Magento\Customer\Model\Metadata\AttributeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new CustomerMetadataManagement(
            $this->attributeResolverMock
        );
    }

    public function testCanBeSearchableInGrid()
    {
        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(\Magento\Customer\Api\Data\AttributeMetadataInterface::class)
            ->getMockForAbstractClass();

        /** @var Attribute|\PHPUnit_Framework_MockObject_MockObject $modelMock */
        $modelMock = $this->getMockBuilder(\Magento\Customer\Model\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeResolverMock->expects($this->once())
            ->method('getModelByAttribute')
            ->with(CustomerMetadataManagement::ENTITY_TYPE_CUSTOMER, $attributeMock)
            ->willReturn($modelMock);

        $modelMock->expects($this->once())
            ->method('canBeSearchableInGrid')
            ->willReturn(true);

        $this->assertTrue($this->model->canBeSearchableInGrid($attributeMock));
    }

    public function testCanBeFilterableInGrid()
    {
        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(\Magento\Customer\Api\Data\AttributeMetadataInterface::class)
            ->getMockForAbstractClass();

        /** @var Attribute|\PHPUnit_Framework_MockObject_MockObject $modelMock */
        $modelMock = $this->getMockBuilder(\Magento\Customer\Model\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeResolverMock->expects($this->once())
            ->method('getModelByAttribute')
            ->with(CustomerMetadataManagement::ENTITY_TYPE_CUSTOMER, $attributeMock)
            ->willReturn($modelMock);

        $modelMock->expects($this->once())
            ->method('canBeFilterableInGrid')
            ->willReturn(true);

        $this->assertTrue($this->model->canBeFilterableInGrid($attributeMock));
    }
}

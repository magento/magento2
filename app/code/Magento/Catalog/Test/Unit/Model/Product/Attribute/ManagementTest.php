<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product\Attribute\Management;
use Magento\Eav\Api\AttributeManagementInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ManagementTest extends TestCase
{
    /**
     * @var Management
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $attrManagementMock;

    protected function setUp(): void
    {
        $this->attrManagementMock = $this->getMockForAbstractClass(AttributeManagementInterface::class);
        $this->model = new Management($this->attrManagementMock);
    }

    public function testAssign()
    {
        $attributeSetId = 1;
        $attributeGroupId = 2;
        $attributeCode = 'attribute_code';
        $sortOrder = 500;

        $this->attrManagementMock->expects($this->once())
            ->method('assign')
            ->with(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeSetId,
                $attributeGroupId,
                $attributeCode,
                $sortOrder
            )->willReturn(1);

        $this->assertEquals(1, $this->model->assign($attributeSetId, $attributeGroupId, $attributeCode, $sortOrder));
    }

    public function testUnassign()
    {
        $attributeSetId = 1;
        $attributeCode = 'attribute_code';
        $this->attrManagementMock->expects($this->once())
            ->method('unassign')
            ->with($attributeSetId, $attributeCode)
            ->willReturn(1);

        $this->assertEquals(1, $this->model->unassign($attributeSetId, $attributeCode));
    }

    public function testGetAttributes()
    {
        $attributeSetId = 1;
        $attributeMock = $this->getMockForAbstractClass(ProductAttributeInterface::class);

        $this->attrManagementMock->expects($this->once())
            ->method('getAttributes')
            ->with(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeSetId
            )->willReturn([$attributeMock]);
        $this->assertEquals([$attributeMock], $this->model->getAttributes($attributeSetId));
    }
}

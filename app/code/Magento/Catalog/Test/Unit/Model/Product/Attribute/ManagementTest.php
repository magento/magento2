<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute;

class ManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Management
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attrManagementMock;

    protected function setUp()
    {
        $this->attrManagementMock = $this->createMock(\Magento\Eav\Api\AttributeManagementInterface::class);
        $this->model = new \Magento\Catalog\Model\Product\Attribute\Management($this->attrManagementMock);
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
                \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
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
        $attributeMock = $this->createMock(\Magento\Catalog\Api\Data\ProductAttributeInterface::class);

        $this->attrManagementMock->expects($this->once())
            ->method('getAttributes')
            ->with(
                \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeSetId
            )->willReturn([$attributeMock]);
        $this->assertEquals([$attributeMock], $this->model->getAttributes($attributeSetId));
    }
}

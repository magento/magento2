<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Product\Attribute;

class ManagementTest extends \PHPUnit_Framework_TestCase
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
        $this->attrManagementMock = $this->getMock('\Magento\Eav\Api\AttributeManagementInterface');
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
        $attributeMock = $this->getMock('\Magento\Catalog\Api\Data\ProductAttributeInterface');

        $this->attrManagementMock->expects($this->once())
            ->method('getAttributes')
            ->with(
                \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeSetId
            )->willReturn([$attributeMock]);
        $this->assertEquals([$attributeMock], $this->model->getAttributes($attributeSetId));
    }
}

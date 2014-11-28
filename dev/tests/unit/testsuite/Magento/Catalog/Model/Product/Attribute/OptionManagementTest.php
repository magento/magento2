<?php
/**
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

class OptionManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\OptionManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavOptionManagementMock;

    protected function setUp()
    {
        $this->eavOptionManagementMock = $this->getMock('\Magento\Eav\Api\AttributeOptionManagementInterface');
        $this->model = new \Magento\Catalog\Model\Product\Attribute\OptionManagement(
            $this->eavOptionManagementMock
        );
    }

    public function testGetItems()
    {
        $attributeCode = 10;
        $this->eavOptionManagementMock->expects($this->once())
            ->method('getItems')
            ->with(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
            ->willReturn([]);
        $this->assertEquals([], $this->model->getItems($attributeCode));
    }

    public function testAdd()
    {
        $attributeCode = 42;
        $optionMock = $this->getMock('\Magento\Eav\Api\Data\AttributeOptionInterface');
        $this->eavOptionManagementMock->expects($this->once())->method('add')->with(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode,
            $optionMock
        )->willReturn(true);
        $this->assertTrue($this->model->add($attributeCode, $optionMock));
    }

    public function testDelete()
    {
        $attributeCode = 'atrCde';
        $optionId = 'opt';
        $this->eavOptionManagementMock->expects($this->once())->method('delete')->with(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode,
            $optionId
        )->willReturn(true);
        $this->assertTrue($this->model->delete($attributeCode, $optionId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid option id
     */
    public function testDeleteWithInvalidOption()
    {
        $attributeCode = 'atrCde';
        $optionId = '';
        $this->eavOptionManagementMock->expects($this->never())->method('delete');
        $this->model->delete($attributeCode, $optionId);
    }
}

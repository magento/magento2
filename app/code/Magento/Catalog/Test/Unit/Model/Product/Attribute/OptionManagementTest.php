<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product\Attribute\OptionManagement;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Api\AttributeOptionUpdateInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class to test management of attribute options
 */
class OptionManagementTest extends TestCase
{
    /**
     * @var OptionManagement
     */
    protected $model;

    /**
     * @var AttributeOptionManagementInterface|MockObject
     */
    protected $eavOptionManagementMock;

    /**
     * @var AttributeOptionUpdateInterface|MockObject
     */
    private $eavOptionUpdateMock;

    protected function setUp(): void
    {
        $this->eavOptionManagementMock = $this->getMockForAbstractClass(AttributeOptionManagementInterface::class);
        $this->eavOptionUpdateMock = $this->getMockForAbstractClass(AttributeOptionUpdateInterface::class);
        $this->model = new OptionManagement(
            $this->eavOptionManagementMock,
            $this->eavOptionUpdateMock
        );
    }

    /**
     * Test to Retrieve list of attribute options
     */
    public function testGetItems()
    {
        $attributeCode = 10;
        $this->eavOptionManagementMock->expects($this->once())
            ->method('getItems')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
            ->willReturn([]);
        $this->assertEquals([], $this->model->getItems($attributeCode));
    }

    /**
     * Test to Add option to attribute
     */
    public function testAdd()
    {
        $attributeCode = 42;
        $optionMock = $this->getMockForAbstractClass(AttributeOptionInterface::class);
        $this->eavOptionManagementMock->expects($this->once())->method('add')->with(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode,
            $optionMock
        )->willReturn(true);
        $this->assertTrue($this->model->add($attributeCode, $optionMock));
    }

    /**
     * Test to delete attribute option
     */
    public function testDelete()
    {
        $attributeCode = 'atrCde';
        $optionId = 'opt';
        $this->eavOptionManagementMock->expects($this->once())->method('delete')->with(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode,
            $optionId
        )->willReturn(true);
        $this->assertTrue($this->model->delete($attributeCode, $optionId));
    }

    /**
     * Test to delete attribute option with invalid option id
     */
    public function testDeleteWithInvalidOption()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('Invalid option id');
        $attributeCode = 'atrCde';
        $optionId = '';
        $this->eavOptionManagementMock->expects($this->never())->method('delete');
        $this->model->delete($attributeCode, $optionId);
    }

    /**
     * Test to update attribute option
     */
    public function testUpdate()
    {
        $attributeCode = 'atrCde';
        $optionId = 10;
        $optionMock = $this->getMockForAbstractClass(AttributeOptionInterface::class);

        $this->eavOptionUpdateMock->expects($this->once())
            ->method('update')
            ->with(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeCode,
                $optionId,
                $optionMock
            )->willReturn(true);
        $this->assertTrue($this->model->update($attributeCode, $optionId, $optionMock));
    }
}

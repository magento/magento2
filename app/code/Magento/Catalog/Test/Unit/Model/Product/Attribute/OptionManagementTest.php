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
use Magento\Eav\Api\Data\AttributeOptionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OptionManagementTest extends TestCase
{
    /**
     * @var OptionManagement
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $eavOptionManagementMock;

    protected function setUp(): void
    {
        $this->eavOptionManagementMock = $this->getMockForAbstractClass(AttributeOptionManagementInterface::class);
        $this->model = new OptionManagement(
            $this->eavOptionManagementMock
        );
    }

    public function testGetItems()
    {
        $attributeCode = 10;
        $this->eavOptionManagementMock->expects($this->once())
            ->method('getItems')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
            ->willReturn([]);
        $this->assertEquals([], $this->model->getItems($attributeCode));
    }

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

    public function testDeleteWithInvalidOption()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('Invalid option id');
        $attributeCode = 'atrCde';
        $optionId = '';
        $this->eavOptionManagementMock->expects($this->never())->method('delete');
        $this->model->delete($attributeCode, $optionId);
    }
}

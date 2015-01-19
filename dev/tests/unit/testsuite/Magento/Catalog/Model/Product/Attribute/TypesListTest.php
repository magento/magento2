<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute;

class TypesListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TypesList
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $inputTypeFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeTypeBuilderMock;

    protected function setUp()
    {
        $this->inputTypeFactoryMock = $this->getMock(
            'Magento\Catalog\Model\Product\Attribute\Source\InputtypeFactory',
            ['create', '__wakeup'],
            [],
            '',
            false);
        $this->attributeTypeBuilderMock =
            $this->getMock(
                'Magento\Catalog\Api\Data\ProductAttributeTypeDataBuilder',
                [
                    'populateWithArray',
                    'create',
                    '__wakeup'
                ],
                [],
                '',
                false);

        $this->model = new TypesList($this->inputTypeFactoryMock, $this->attributeTypeBuilderMock);
    }

    public function testGetItems()
    {
        $inputTypeMock = $this->getMock('Magento\Catalog\Model\Product\Attribute\Source\Inputtype', [], [], '', false);
        $this->inputTypeFactoryMock->expects($this->once())->method('create')->willReturn($inputTypeMock);
        $inputTypeMock->expects($this->once())->method('toOptionArray')->willReturn(['option' => ['value']]);
        $this->attributeTypeBuilderMock->expects($this->once())
            ->method('populateWithArray')
            ->with(['value'])
            ->willReturnSelf();
        $this->attributeTypeBuilderMock->expects($this->once())->method('create')->willReturnSelf();
        $this->assertEquals([$this->attributeTypeBuilderMock], $this->model->getItems());
    }
}

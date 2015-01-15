<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model;

class OptionTypeListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Model\OptionTypeList
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeBuilderMock;

    protected function setUp()
    {
        $this->typeMock = $this->getMock('\Magento\Bundle\Model\Source\Option\Type', [], [], '', false);
        $this->typeBuilderMock = $this->getMock(
            '\Magento\Bundle\Api\Data\OptionTypeDataBuilder',
            ['setCode', 'setLabel', 'create'],
            [],
            '',
            false
        );
        $this->model = new \Magento\Bundle\Model\OptionTypeList(
            $this->typeMock,
            $this->typeBuilderMock
        );
    }

    public function testGetItems()
    {
        $this->typeMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn([['value' => 'value', 'label' => 'label']]);

        $typeMock = $this->getMock('\Magento\Bundle\Api\Data\OptionTypeInterface');
        $this->typeBuilderMock->expects($this->once())->method('setCode')->with('value')->willReturnSelf();
        $this->typeBuilderMock->expects($this->once())->method('setLabel')->with('label')->willReturnSelf();
        $this->typeBuilderMock->expects($this->once())->method('create')->willReturn($typeMock);
        $this->assertEquals([$typeMock], $this->model->getItems());
    }
}

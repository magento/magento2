<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model;

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
    protected $typeFactoryMock;

    protected function setUp()
    {
        $this->typeMock = $this->getMock('\Magento\Bundle\Model\Source\Option\Type', [], [], '', false);
        $this->typeFactoryMock = $this->getMock(
            '\Magento\Bundle\Api\Data\OptionTypeInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->model = new \Magento\Bundle\Model\OptionTypeList(
            $this->typeMock,
            $this->typeFactoryMock
        );
    }

    public function testGetItems()
    {
        $this->typeMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn([['value' => 'value', 'label' => 'label']]);

        $typeMock = $this->getMock('\Magento\Bundle\Api\Data\OptionTypeInterface');
        $typeMock->expects($this->once())->method('setCode')->with('value')->willReturnSelf();
        $typeMock->expects($this->once())->method('setLabel')->with('label')->willReturnSelf();
        $this->typeFactoryMock->expects($this->once())->method('create')->willReturn($typeMock);
        $this->assertEquals([$typeMock], $this->model->getItems());
    }
}

<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model;

class OptionTypeListTest extends \PHPUnit\Framework\TestCase
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
        $this->typeMock = $this->createMock(\Magento\Bundle\Model\Source\Option\Type::class);
        $this->typeFactoryMock = $this->createPartialMock(
            \Magento\Bundle\Api\Data\OptionTypeInterfaceFactory::class,
            ['create']
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

        $typeMock = $this->createMock(\Magento\Bundle\Api\Data\OptionTypeInterface::class);
        $typeMock->expects($this->once())->method('setCode')->with('value')->willReturnSelf();
        $typeMock->expects($this->once())->method('setLabel')->with('label')->willReturnSelf();
        $this->typeFactoryMock->expects($this->once())->method('create')->willReturn($typeMock);
        $this->assertEquals([$typeMock], $this->model->getItems());
    }
}

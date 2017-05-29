<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Layout\Argument\Interpreter;

use \Magento\Framework\View\Layout\Argument\Interpreter\DataObject;

class ObjectTest extends \PHPUnit_Framework_TestCase
{
    const EXPECTED_CLASS = \Magento\Framework\View\Test\Unit\Layout\Argument\Interpreter\ObjectTest::class;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Data\Argument\InterpreterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_interpreter;

    /**
     * @var DataObject
     */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_model = new DataObject($this->_objectManager, self::EXPECTED_CLASS);
    }

    public function testEvaluate()
    {
        $input = ['name' => 'dataSource', 'value' => self::EXPECTED_CLASS];
        $this->_objectManager->expects($this->once())
            ->method('create')
            ->with(self::EXPECTED_CLASS)
            ->willReturn($this);

        $actual = $this->_model->evaluate($input);
        $this->assertSame($this, $actual);
    }

    /**
     * @dataProvider evaluateWrongClassDataProvider
     */
    public function testEvaluateWrongClass($input, $expectedException, $expectedExceptionMessage)
    {
        $this->setExpectedException($expectedException, $expectedExceptionMessage);
        $self = $this;
        $this->_objectManager->expects($this->any())->method('create')->willReturnCallback(
            function ($className) use ($self) {
                return $self->getMock($className);
            }
        );

        $this->_model->evaluate($input);
    }

    /**
     * @return array
     */
    public function evaluateWrongClassDataProvider()
    {
        return [
            'no class' => [[], '\InvalidArgumentException', 'Object class name is missing'],
            'unexpected class' => [
                ['value' => \Magento\Framework\ObjectManagerInterface::class],
                '\UnexpectedValueException',
                'Instance of ' . self::EXPECTED_CLASS . ' is expected',
            ]
        ];
    }
}

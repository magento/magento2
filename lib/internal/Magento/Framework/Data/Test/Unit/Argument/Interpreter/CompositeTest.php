<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit\Argument\Interpreter;

use \Magento\Framework\Data\Argument\Interpreter\Composite;

class CompositeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Data\Argument\InterpreterInterface
     */
    protected $_interpreterOne;

    /**
     * @var \Magento\Framework\Data\Argument\InterpreterInterface
     */
    protected $_interpreterTwo;

    /**
     * @var Composite
     */
    protected $_model;

    protected function setUp()
    {
        $this->_interpreterOne = $this->createMock(\Magento\Framework\Data\Argument\InterpreterInterface::class);
        $this->_interpreterTwo = $this->createMock(\Magento\Framework\Data\Argument\InterpreterInterface::class);
        $this->_model = new Composite(
            ['one' => $this->_interpreterOne, 'two' => $this->_interpreterTwo],
            'interpreter'
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Interpreter named 'wrong' is expected to be an argument interpreter instance
     */
    public function testConstructWrongInterpreter()
    {
        $interpreters = [
            'correct' => $this->createMock(\Magento\Framework\Data\Argument\InterpreterInterface::class),
            'wrong' => $this->createMock(\Magento\Framework\ObjectManagerInterface::class),
        ];
        new Composite($interpreters, 'interpreter');
    }

    /**
     * @param array $input
     * @param string $expectedExceptionMessage
     *
     * @dataProvider evaluateWrongDiscriminatorDataProvider
     */
    public function testEvaluateWrongDiscriminator($input, $expectedExceptionMessage)
    {
        $this->expectException('\InvalidArgumentException', $expectedExceptionMessage);
        $this->_model->evaluate($input);
    }

    public function evaluateWrongDiscriminatorDataProvider()
    {
        return [
            'no discriminator' => [[], 'Value for key "interpreter" is missing in the argument data'],
            'nonexistent interpreter ' => [
                ['interpreter' => 'nonexistent'],
                "Argument interpreter named 'nonexistent' has not been defined",
            ]
        ];
    }

    public function testEvaluate()
    {
        $input = ['interpreter' => 'one', 'value' => 'test'];
        $expected = ['value' => 'test (updated)'];

        $this->_interpreterOne->expects(
            $this->once()
        )->method(
            'evaluate'
        )->with(
            ['value' => 'test']
        )->will(
            $this->returnValue($expected)
        );
        $this->assertSame($expected, $this->_model->evaluate($input));
    }

    public function testAddInterpreter()
    {
        $input = ['interpreter' => 'new', 'value' => 'test'];
        $newInterpreter = $this->createMock(\Magento\Framework\Data\Argument\InterpreterInterface::class);
        $this->_model->addInterpreter('new', $newInterpreter);
        $newInterpreter->expects($this->once())->method('evaluate')->with(['value' => 'test']);
        $this->_model->evaluate($input);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Argument interpreter named 'one' has already been defined
     *
     */
    public function testAddInterpreterException()
    {
        $newInterpreter = $this->createMock(\Magento\Framework\Data\Argument\InterpreterInterface::class);
        $this->_model->addInterpreter('one', $newInterpreter);
    }
}

<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit\Argument\Interpreter;

use \Magento\Framework\Data\Argument\Interpreter\Composite;

class CompositeTest extends \PHPUnit_Framework_TestCase
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
        $this->_interpreterOne = $this->getMock('Magento\Framework\Data\Argument\InterpreterInterface');
        $this->_interpreterTwo = $this->getMock('Magento\Framework\Data\Argument\InterpreterInterface');
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
            'correct' => $this->getMock('Magento\Framework\Data\Argument\InterpreterInterface'),
            'wrong' => $this->getMock('Magento\Framework\ObjectManagerInterface'),
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
        $this->setExpectedException('\InvalidArgumentException', $expectedExceptionMessage);
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
        $newInterpreter = $this->getMock('Magento\Framework\Data\Argument\InterpreterInterface');
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
        $newInterpreter = $this->getMock('Magento\Framework\Data\Argument\InterpreterInterface');
        $this->_model->addInterpreter('one', $newInterpreter);
    }
}

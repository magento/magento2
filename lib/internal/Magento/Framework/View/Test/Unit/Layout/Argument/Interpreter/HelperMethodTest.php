<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Layout\Argument\Interpreter;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Layout\Argument\Interpreter\HelperMethod;
use Magento\Framework\View\Layout\Argument\Interpreter\NamedParams;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HelperMethodTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $_objectManager;

    /**
     * @var NamedParams|MockObject
     */
    protected $_interpreter;

    /**
     * @var HelperMethod
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->_interpreter = $this->createMock(NamedParams::class);
        $this->_model = new HelperMethod($this->_objectManager, $this->_interpreter);
    }

    public function testEvaluate()
    {
        $input = ['value' => 'some text', 'helper' => __CLASS__ . '::help'];

        $evaluatedValue = ['value' => 'some text (evaluated)'];
        $this->_interpreter->expects(
            $this->once()
        )->method(
            'evaluate'
        )->with(
            $input
        )->willReturn(
            $evaluatedValue
        );

        $this->_objectManager->expects($this->once())->method('get')->with(__CLASS__)->willReturn($this);

        $expected = 'some text (evaluated) (updated)';
        $actual = $this->_model->evaluate($input);
        $this->assertSame($expected, $actual);
    }

    /**
     * @param $input
     * @return string
     */
    public function help($input)
    {
        $this->assertSame('some text (evaluated)', $input);
        return $input . ' (updated)';
    }

    /**
     * @param string $helperMethod
     * @param string $expectedExceptionMessage
     *
     * @dataProvider evaluateExceptionDataProvider
     */
    public function testEvaluateException($helperMethod, $expectedExceptionMessage)
    {
        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage($expectedExceptionMessage);
        $input = ['value' => 'some text', 'helper' => $helperMethod];
        $this->_model->evaluate($input);
    }

    /**
     * @return array
     */
    public static function evaluateExceptionDataProvider()
    {
        $nonExistingHelper = __CLASS__ . '::non_existing';
        return [
            'wrong method format' => [
                'help',
                'Helper method name in format "\Class\Name::methodName" is expected',
            ],
            'non-existing method' => [$nonExistingHelper, "Helper method '{$nonExistingHelper}' does not exist"]
        ];
    }
}

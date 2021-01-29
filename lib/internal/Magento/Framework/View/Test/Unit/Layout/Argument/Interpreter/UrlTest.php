<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Layout\Argument\Interpreter;

use \Magento\Framework\View\Layout\Argument\Interpreter\Url;

class UrlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_urlResolver;

    /**
     * @var \Magento\Framework\View\Layout\Argument\Interpreter\NamedParams|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_interpreter;

    /**
     * @var Url
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_urlResolver = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->_interpreter = $this->createMock(\Magento\Framework\View\Layout\Argument\Interpreter\NamedParams::class);
        $this->_model = new Url($this->_urlResolver, $this->_interpreter);
    }

    public function testEvaluate()
    {
        $input = ['path' => 'some/path'];
        $expected = 'http://some.domain.com/some/path/';

        $urlParams = ['param'];
        $this->_interpreter->expects(
            $this->once()
        )->method(
            'evaluate'
        )->with(
            $input
        )->willReturn(
            $urlParams
        );

        $this->_urlResolver->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            'some/path',
            $urlParams
        )->willReturn(
            $expected
        );

        $actual = $this->_model->evaluate($input);
        $this->assertSame($expected, $actual);
    }

    /**
     */
    public function testEvaluateWrongPath()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('URL path is missing');

        $input = [];
        $this->_model->evaluate($input);
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Layout\Argument\Interpreter;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Layout\Argument\Interpreter\NamedParams;
use Magento\Framework\View\Layout\Argument\Interpreter\Url;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    /**
     * @var UrlInterface|MockObject
     */
    protected $_urlResolver;

    /**
     * @var NamedParams|MockObject
     */
    protected $_interpreter;

    /**
     * @var Url
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_urlResolver = $this->getMockForAbstractClass(UrlInterface::class);
        $this->_interpreter = $this->createMock(NamedParams::class);
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

    public function testEvaluateWrongPath()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('URL path is missing');
        $input = [];
        $this->_model->evaluate($input);
    }
}

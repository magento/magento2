<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Layout\Argument\Interpreter;

use \Magento\Framework\View\Layout\Argument\Interpreter\Url;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlResolver;

    /**
     * @var \Magento\Framework\View\Layout\Argument\Interpreter\NamedParams|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_interpreter;

    /**
     * @var Url
     */
    protected $_model;

    protected function setUp()
    {
        $this->_urlResolver = $this->getMock('Magento\Framework\UrlInterface');
        $this->_interpreter = $this->getMock(
            'Magento\Framework\View\Layout\Argument\Interpreter\NamedParams',
            [],
            [],
            '',
            false
        );
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
        )->will(
            $this->returnValue($urlParams)
        );

        $this->_urlResolver->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            'some/path',
            $urlParams
        )->will(
            $this->returnValue($expected)
        );

        $actual = $this->_model->evaluate($input);
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage URL path is missing
     */
    public function testEvaluateWrongPath()
    {
        $input = [];
        $this->_model->evaluate($input);
    }
}

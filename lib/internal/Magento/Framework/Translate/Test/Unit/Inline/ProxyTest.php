<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Translate\Test\Unit\Inline;

use \Magento\Framework\Translate\Inline\Proxy;

class ProxyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Translate\Inline|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translateMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->translateMock = $this->createMock(\Magento\Framework\Translate\Inline::class);
    }

    public function testIsAllowed()
    {
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            \Magento\Framework\Translate\Inline::class
        )->will(
            $this->returnValue($this->translateMock)
        );
        $this->objectManagerMock->expects($this->never())->method('create');
        $this->translateMock->expects($this->once())->method('isAllowed')->will($this->returnValue(false));

        $model = new Proxy(
            $this->objectManagerMock,
            \Magento\Framework\Translate\Inline::class,
            true
        );

        $this->assertFalse($model->isAllowed());
    }

    public function testGetParser()
    {
        $parser = new \stdClass();
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            \Magento\Framework\Translate\Inline::class
        )->will(
            $this->returnValue($this->translateMock)
        );
        $this->objectManagerMock->expects($this->never())->method('get');
        $this->translateMock->expects($this->once())->method('getParser')->will($this->returnValue($parser));

        $model = new Proxy(
            $this->objectManagerMock,
            \Magento\Framework\Translate\Inline::class,
            false
        );

        $this->assertEquals($parser, $model->getParser());
    }

    public function testProcessResponseBody()
    {
        $isJson = true;
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            \Magento\Framework\Translate\Inline::class
        )->will(
            $this->returnValue($this->translateMock)
        );
        $this->objectManagerMock->expects($this->never())->method('create');

        $this->translateMock->expects($this->once())
            ->method('processResponseBody')
            ->with('', $isJson)
            ->will($this->returnSelf());

        $model = new Proxy(
            $this->objectManagerMock,
            \Magento\Framework\Translate\Inline::class,
            true
        );
        $body = '';

        $this->assertEquals($this->translateMock, $model->processResponseBody($body, $isJson));
    }

    public function testGetAdditionalHtmlAttribute()
    {
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            \Magento\Framework\Translate\Inline::class
        )->will(
            $this->returnValue($this->translateMock)
        );
        $this->objectManagerMock->expects($this->never())->method('get');
        $this->translateMock->expects($this->exactly(2))
            ->method('getAdditionalHtmlAttribute')
            ->with($this->logicalOr('some_value', null))
            ->will($this->returnArgument(0));

        $model = new Proxy(
            $this->objectManagerMock,
            \Magento\Framework\Translate\Inline::class,
            false
        );

        $this->assertEquals('some_value', $model->getAdditionalHtmlAttribute('some_value'));
        $this->assertNull($model->getAdditionalHtmlAttribute());
    }
}

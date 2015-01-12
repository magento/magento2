<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Translate\Inline;

class ProxyTest extends \PHPUnit_Framework_TestCase
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
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->translateMock = $this->getMock('Magento\Framework\Translate\Inline', [], [], '', false);
    }

    public function testIsAllowed()
    {
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'Magento\Framework\Translate\Inline'
        )->will(
            $this->returnValue($this->translateMock)
        );
        $this->objectManagerMock->expects($this->never())->method('create');
        $this->translateMock->expects($this->once())->method('isAllowed')->will($this->returnValue(false));

        $model = new Proxy(
            $this->objectManagerMock,
            'Magento\Framework\Translate\Inline',
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
            'Magento\Framework\Translate\Inline'
        )->will(
            $this->returnValue($this->translateMock)
        );
        $this->objectManagerMock->expects($this->never())->method('get');
        $this->translateMock->expects($this->once())->method('getParser')->will($this->returnValue($parser));

        $model = new Proxy(
            $this->objectManagerMock,
            'Magento\Framework\Translate\Inline',
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
            'Magento\Framework\Translate\Inline'
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
            'Magento\Framework\Translate\Inline',
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
            'Magento\Framework\Translate\Inline'
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
            'Magento\Framework\Translate\Inline',
            false
        );

        $this->assertEquals('some_value', $model->getAdditionalHtmlAttribute('some_value'));
        $this->assertNull($model->getAdditionalHtmlAttribute());
    }
}

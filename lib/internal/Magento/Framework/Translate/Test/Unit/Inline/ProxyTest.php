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
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Translate\Inline|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $translateMock;

    protected function setUp(): void
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
        )->willReturn(
            $this->translateMock
        );
        $this->objectManagerMock->expects($this->never())->method('create');
        $this->translateMock->expects($this->once())->method('isAllowed')->willReturn(false);

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
        )->willReturn(
            $this->translateMock
        );
        $this->objectManagerMock->expects($this->never())->method('get');
        $this->translateMock->expects($this->once())->method('getParser')->willReturn($parser);

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
        )->willReturn(
            $this->translateMock
        );
        $this->objectManagerMock->expects($this->never())->method('create');

        $this->translateMock->expects($this->once())
            ->method('processResponseBody')
            ->with('', $isJson)
            ->willReturnSelf();

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
        )->willReturn(
            $this->translateMock
        );
        $this->objectManagerMock->expects($this->never())->method('get');
        $this->translateMock->expects($this->exactly(2))
            ->method('getAdditionalHtmlAttribute')
            ->with($this->logicalOr('some_value', null))
            ->willReturnArgument(0);

        $model = new Proxy(
            $this->objectManagerMock,
            \Magento\Framework\Translate\Inline::class,
            false
        );

        $this->assertEquals('some_value', $model->getAdditionalHtmlAttribute('some_value'));
        $this->assertNull($model->getAdditionalHtmlAttribute());
    }
}

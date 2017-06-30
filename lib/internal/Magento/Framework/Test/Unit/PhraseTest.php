<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit;

use Magento\Framework\Phrase;

class PhraseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Phrase\RendererInterface
     */
    protected $defaultRenderer;

    /**
     * @var \Magento\Framework\Phrase\RendererInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rendererMock;

    /**
     * SetUp method
     *
     * @return void
     */
    protected function setUp()
    {
        $this->defaultRenderer = Phrase::getRenderer();
        $this->rendererMock = $this->getMockBuilder(\Magento\Framework\Phrase\RendererInterface::class)
            ->getMock();
    }

    /**
     * Tear down
     *
     * @return void
     */
    protected function tearDown()
    {
        Phrase::setRenderer($this->defaultRenderer);
    }

    /**
     * Test rendering
     *
     * @return void
     */
    public function testRendering()
    {
        $text = 'some text';
        $arguments = ['arg1', 'arg2'];
        $result = 'rendered text';
        $phrase = new Phrase($text, $arguments);
        Phrase::setRenderer($this->rendererMock);

        $this->rendererMock->expects($this->once())
            ->method('render')
            ->with([$text], $arguments)
            ->willReturn($result);

        $this->assertEquals($result, $phrase->render());
    }

    /**
     * Test defers rendering
     *
     * @return void
     */
    public function testDefersRendering()
    {
        $this->rendererMock->expects($this->never())
            ->method('render');

        new Phrase('some text');
    }

    /**
     * Test that to string is alias to render
     *
     * @return void
     */
    public function testThatToStringIsAliasToRender()
    {
        $text = 'some text';
        $arguments = ['arg1', 'arg2'];
        $result = 'rendered text';
        $phrase = new Phrase($text, $arguments);
        Phrase::setRenderer($this->rendererMock);

        $this->rendererMock->expects($this->once())
            ->method('render')
            ->with([$text], $arguments)
            ->willReturn($result);

        $this->assertEquals($result, (string)$phrase);
    }

    /**
     * Test get text
     *
     * @return void
     */
    public function testGetText()
    {
        $text = 'some text';
        $phrase = new Phrase($text);

        $this->assertEquals($text, $phrase->getText());
    }

    /**
     * Test get arguments
     *
     * @return void
     */
    public function testGetArguments()
    {
        $text = 'some text';
        $arguments = ['arg1', 'arg2'];
        $phrase1 = new Phrase($text);
        $phrase2 = new Phrase($text, $arguments);

        $this->assertEquals([], $phrase1->getArguments());
        $this->assertEquals($arguments, $phrase2->getArguments());
    }

    public function testToStringWithExceptionOnRender()
    {
        $text = 'raw text';
        $exception = new \Exception('something went wrong');
        $phrase = new Phrase($text);

        $this->rendererMock->expects($this->any())
            ->method('render')
            ->willThrowException($exception);

        $this->assertEquals($text, (string)$phrase);
    }

    /**
     * Test default renderer
     */
    public function testDefaultRenderer()
    {
        $this->assertInstanceOf(\Magento\Framework\Phrase\Renderer\Placeholder::class, Phrase::getRenderer());
    }
}

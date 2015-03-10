<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

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

    protected function setUp()
    {
        $this->defaultRenderer = \Magento\Framework\Phrase::getRenderer();
        $this->rendererMock = $this->getMockBuilder('Magento\Framework\Phrase\RendererInterface')
            ->getMock();
    }

    protected function tearDown()
    {
        \Magento\Framework\Phrase::setRenderer($this->defaultRenderer);
    }

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

    public function testDefersRendering()
    {
        $this->rendererMock->expects($this->never())
            ->method('render');

        new Phrase('some text');
    }

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

    public function testGetText()
    {
        $text = 'some text';
        $phrase = new Phrase($text);

        $this->assertEquals($text, $phrase->getText());
    }

    public function testGetArguments()
    {
        $text = 'some text';
        $arguments = ['arg1', 'arg2'];
        $phrase1 = new Phrase($text);
        $phrase2 = new Phrase($text, $arguments);

        $this->assertEquals([], $phrase1->getArguments());
        $this->assertEquals($arguments, $phrase2->getArguments());
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

class PhraseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Phrase
     */
    protected $phrase;

    /**
     * @var \Magento\Framework\Phrase\RendererInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $renderer;

    protected function setUp()
    {
        $this->renderer = $this->getMock('Magento\Framework\Phrase\RendererInterface');
        \Magento\Framework\Phrase::setRenderer($this->renderer);
    }

    protected function tearDown()
    {
        $this->removeRendererFromPhrase();
        \Magento\Framework\Phrase::setRenderer(new \Magento\Framework\Phrase\Renderer\Placeholder());
    }

    public function testRendering()
    {
        $text = 'some text';
        $arguments = ['arg1', 'arg2'];
        $result = 'rendered text';
        $this->phrase = new \Magento\Framework\Phrase($text, $arguments);

        $this->renderer->expects(
            $this->once()
        )->method(
            'render'
        )->with(
            [$text],
            $arguments
        )->will(
            $this->returnValue($result)
        );

        $this->assertEquals($result, $this->phrase->render());
    }

    public function testRenderingWithoutRenderer()
    {
        $this->removeRendererFromPhrase();
        $result = 'some text';
        $this->phrase = new \Magento\Framework\Phrase($result);

        $this->assertEquals($result, $this->phrase->render());
    }

    public function testDefersRendering()
    {
        $this->renderer->expects($this->never())->method('render');
        $this->phrase = new \Magento\Framework\Phrase('some text');
    }

    public function testThatToStringIsAliasToRender()
    {
        $text = 'some text';
        $arguments = ['arg1', 'arg2'];
        $result = 'rendered text';
        $this->phrase = new \Magento\Framework\Phrase($text, $arguments);

        $this->renderer->expects(
            $this->once()
        )->method(
            'render'
        )->with(
            [$text],
            $arguments
        )->will(
            $this->returnValue($result)
        );

        $this->assertEquals($result, (string) $this->phrase);
    }

    protected function removeRendererFromPhrase()
    {
        $property = new \ReflectionProperty('Magento\Framework\Phrase', '_renderer');
        $property->setAccessible(true);
        $property->setValue($this->phrase, null);
    }
}

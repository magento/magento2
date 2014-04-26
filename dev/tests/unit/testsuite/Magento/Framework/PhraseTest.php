<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

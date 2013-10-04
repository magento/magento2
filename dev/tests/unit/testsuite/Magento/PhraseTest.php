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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento;

class PhraseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Phrase\RendererInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_renderer;

    protected function setUp()
    {
        $this->_renderer = $this->getMock('Magento\Phrase\RendererInterface');
        \Magento\Phrase::setRenderer($this->_renderer);
    }

    protected function tearDown()
    {
        $this->_removeRendererFromPhrase();
        \Magento\Phrase::setRenderer(new \Magento\Phrase\Renderer\Placeholder());
    }

    public function testRendering()
    {
        $text = 'some text';
        $arguments = array('arg1', 'arg2');
        $result = 'rendered text';
        $phrase = new \Magento\Phrase($text, $arguments);

        $this->_renderer->expects($this->once())->method('render')->with($text, $arguments)
            ->will($this->returnValue($result));

        $this->assertEquals($result, $phrase->render());
    }

    public function testRenderingWithoutRenderer()
    {
        $this->_removeRendererFromPhrase();
        $result = 'some text';
        $phrase = new \Magento\Phrase($result);

        $this->assertEquals($result, $phrase->render());
    }

    public function testDefersRendering()
    {
        $this->_renderer->expects($this->never())->method('render');

        new \Magento\Phrase('some text');
    }

    public function testThatToStringIsAliasToRender()
    {
        $text = 'some text';
        $arguments = array('arg1', 'arg2');
        $result = 'rendered text';
        $phrase = new \Magento\Phrase($text, $arguments);

        $this->_renderer->expects($this->once())->method('render')->with($text, $arguments)
            ->will($this->returnValue($result));

        $this->assertEquals($result, (string)$phrase);
    }

    protected function _removeRendererFromPhrase()
    {
        $property = new \ReflectionProperty('Magento\Phrase', '_renderer');
        $property->setAccessible(true);
        $property->setValue(null);
    }
}

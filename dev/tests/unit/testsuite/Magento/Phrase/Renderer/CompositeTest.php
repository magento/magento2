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
namespace Magento\Phrase\Renderer;

class CompositeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Phrase\Renderer\Factory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_rendererFactory;

    protected function setUp()
    {
        $this->_rendererFactory = $this->getMock('Magento\Phrase\Renderer\Factory', array(), array(), '', false);
    }

    /**
     * @param array $renderers
     * @return \Magento\Phrase\Renderer\Composite
     */
    protected function _createComposite($renderers = array())
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        return $objectManagerHelper->getObject('Magento\Phrase\Renderer\Composite', array(
            'rendererFactory' => $this->_rendererFactory,
            'renderers' => $renderers,
        ));
    }

    public function testCreatingRenderersWhenCompositeCreating()
    {
        $this->_rendererFactory->expects($this->at(0))->method('create')->with('RenderClass1')
            ->will($this->returnValue($this->getMockForAbstractClass('Magento\Phrase\RendererInterface')));
        $this->_rendererFactory->expects($this->at(1))->method('create')->with('RenderClass2')
            ->will($this->returnValue($this->getMockForAbstractClass('Magento\Phrase\RendererInterface')));

        $this->_createComposite(array('RenderClass1', 'RenderClass2'));
    }

    public function testRender()
    {
        $text = 'some text';
        $arguments = array('arg1', 'arg2');
        $resultAfterFirst = 'rendered text first';
        $resultAfterSecond = 'rendered text second';

        $rendererFirst = $this->getMock('Magento\Phrase\RendererInterface');
        $rendererFirst->expects($this->once())->method('render')->with($text, $arguments)
            ->will($this->returnValue($resultAfterFirst));

        $rendererSecond = $this->getMock('Magento\Phrase\RendererInterface');
        $rendererSecond->expects($this->once())->method('render')->with($resultAfterFirst, $arguments)
            ->will($this->returnValue($resultAfterSecond));

        $this->_rendererFactory->expects($this->at(0))->method('create')->with('RenderClass1')
            ->will($this->returnValue($rendererFirst));
        $this->_rendererFactory->expects($this->at(1))->method('create')->with('RenderClass2')
            ->will($this->returnValue($rendererSecond));

        $this->assertEquals(
            $resultAfterSecond,
            $this->_createComposite(array('RenderClass1', 'RenderClass2'))->render($text, $arguments)
        );
    }
}

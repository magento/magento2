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
namespace Magento\Core\Model\TemplateEngine;

class TwigTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Core\Model\TemplateEngine\Twig */
    protected $_twigEngine;

    /** @var  \PHPUnit_Framework_MockObject_MockObject \Magento\Core\Model\TemplateEngine_EnvironmentFactory */
    protected $_envFactoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $_extMock;

    /**
     * Create a Twig template engine to test.
     */
    protected function setUp()
    {
        // Objects that are injected into \Magento\Core\Model\TemplateEngine\Twig
        $this->_envFactoryMock = $this->getMockBuilder('Magento\Core\Model\TemplateEngine\Twig\EnvironmentFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_extMock = $this->getMockBuilder('Magento\Core\Model\TemplateEngine\Twig\Extension')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_twigEngine
            = new \Magento\Core\Model\TemplateEngine\Twig($this->_envFactoryMock, $this->_extMock);
    }

    /**
     * Test the render() function with a very simple .twig file.
     */
    public function testRenderPositive()
    {
        $renderedOutput = '<html></html>';
        $blockMock = $this->getMockBuilder('Magento\Core\Block\Template')
            ->disableOriginalConstructor()->getMock();
        $environmentMock = $this->getMockBuilder('Twig_Environment')->disableOriginalConstructor()->getMock();
        $environmentMock->expects($this->once())->method('render')->will($this->returnValue($renderedOutput));
        $this->_envFactoryMock->expects($this->once())->method('create')->will(
            $this->returnValue($environmentMock)
        );
        $actualOutput = $this->_twigEngine->render($blockMock, '');
        $this->assertSame($renderedOutput, $actualOutput, 'Twig file did not render properly');
    }

    /**
     * Test the render() function such that it throws an exception
     * 
     * @expectedException \Magento\Exception
     */    
    public function testRenderNegative() 
    {
        $blockMock = $this->getMockBuilder('Magento\Core\Block\Template')
        ->disableOriginalConstructor()->getMock();
        $environmentMock = $this->getMockBuilder('Twig_Environment')
            ->disableOriginalConstructor()->getMock();
        $environmentMock->expects($this->once())
            ->method('render')
            ->will($this->throwException(new \Magento\Exception()));
        $this->_envFactoryMock->expects($this->once())->method('create')->will(
                $this->returnValue($environmentMock)
        );
        $this->_twigEngine->render($blockMock, '');
    }
    
    /**
     * Test the getCurrentBlock function.
     *
     * Since its set/reset during render(), make sure it does not return anything when empty.
     */
    public function testGetCurrentBlock()
    {
        $block = $this->_twigEngine->getCurrentBlock();
        $this->assertNull($block);
    }
}

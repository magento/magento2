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
 * obtain it through the world-wide-web, please send an e-mail
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

class FactoryTest extends \PHPUnit_Framework_TestCase
{

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_objectManagerMock;

    /** @var  \Magento\Core\Model\TemplateEngine\Factory */
    protected $_factory;

    /**
     * Setup a factory to test with an mocked object manager.
     */
    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\ObjectManager');
        $this->_factory = new \Magento\Core\Model\TemplateEngine\Factory($this->_objectManagerMock);
    }

    /**
     * Test getting a phtml engine
     */
    public function testGetPhtmlEngine()
    {
        $phtmlEngineMock = $this->getMock('Magento\Core\Model\TemplateEngine\Php');
        $this->_objectManagerMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo('Magento\Core\Model\TemplateEngine\Php'))
            ->will($this->returnValue($phtmlEngineMock));
        $actual = $this->_factory->get(\Magento\Core\Model\TemplateEngine\Factory::ENGINE_PHTML);
        $this->assertSame($phtmlEngineMock, $actual, 'phtml engine not returned');
    }

    /**
     * Test getting a Twig engine
     */
    public function testGetTwigEngine()
    {
        $twigEngineMock = $this->getMockBuilder('Magento\Core\Model\TemplateEngine\Twig')
            ->disableOriginalConstructor()->getMock();
        $this->_objectManagerMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo('Magento\Core\Model\TemplateEngine\Twig'))
            ->will($this->returnValue($twigEngineMock));
        $actual = $this->_factory->get(\Magento\Core\Model\TemplateEngine\Factory::ENGINE_TWIG);
        $this->assertSame($twigEngineMock, $actual, 'phtml engine not returned');
    }

    /**
     * Test attempting to get an engine the factory does not know about (neither Twig nor Phtml.)
     *
     * Should throw an exception
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown template engine type: NotAnEngineName
     */
    public function testGetBadEngine()
    {
        $this->_objectManagerMock->expects($this->never())
            ->method('get');
        $this->_factory->get('NotAnEngineName');
    }

    /**
     * Test attempting to get an engine passing in null as the engine type.
     *
     * Should throw an exception
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown template engine type:
     */
    public function testGetNullEngine()
    {
        $this->_objectManagerMock->expects($this->never())
            ->method('get');
        $this->_factory->get(NULL);
    }
}

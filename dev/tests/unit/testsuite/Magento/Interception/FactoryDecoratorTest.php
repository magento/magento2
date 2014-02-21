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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Interception;

class FactoryDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    /**
     * @var FactoryDecorator
     */
    private $decorator;

    protected function setUp()
    {
        $this->factory = $this->getMockForAbstractClass('\Magento\ObjectManager\Factory');
        $this->config = $this->getMockForAbstractClass('\Magento\Interception\Config');
        $pluginList = $this->getMockForAbstractClass('\Magento\Interception\PluginList');
        $objectManager = $this->getMockForAbstractClass('\Magento\ObjectManager');
        $this->decorator = new FactoryDecorator($this->factory, $this->config, $pluginList, $objectManager);
    }

    public function testCreateDecorated()
    {
        $this->config->expects($this->once())->method('hasPlugins')->with('type')->will($this->returnValue(true));
        $this->config
            ->expects($this->once())
            ->method('getInterceptorClassName')
            ->with('type')
            ->will($this->returnValue('StdClass'))
        ;
        $this->assertInstanceOf('StdClass', $this->decorator->create('type'));
    }

    public function testCreateClean()
    {
        $this->config->expects($this->once())->method('hasPlugins')->with('type')->will($this->returnValue(false));
        $this->config->expects($this->never())->method('getInterceptorClassName');
        $this->factory
            ->expects($this->once())
            ->method('create')
            ->with('type', array(1, 2, 3))
            ->will($this->returnValue('test'))
        ;
        $this->assertEquals('test', $this->decorator->create('type', array(1, 2, 3)));
    }
} 

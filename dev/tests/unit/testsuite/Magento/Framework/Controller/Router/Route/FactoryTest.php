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

namespace Magento\Framework\Controller\Router\Route;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManager
     */
    protected $objectManager;

    public function setUp()
    {
        $this->objectManager = $this->getMockBuilder('Magento\Framework\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @test
     */
    public function testCreateRoute()
    {
        $routerClass = 'router';

        $router = $this->getMockBuilder('Zend_Controller_Router_Route_Interface')
            ->setMockClassName($routerClass)
            ->getMock();

        $parameterRoute    = 'route';
        $parameterDefaults = 'defaults';
        $parameterRegs     = 'regs';
        $parameterLocale   = 'locale';

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(
                $this->logicalOr(
                    $routerClass,
                    array(
                        'route'    => $parameterRoute,
                        'defaults' => $parameterDefaults,
                        'regs'     => $parameterRegs,
                        'locale'   => $parameterLocale,
                    )
                )
            )
            ->will($this->returnValue($router));

        $object = new \Magento\Framework\Controller\Router\Route\Factory($this->objectManager);
        $expectedRouter = $object->createRoute(
            $routerClass,
            $parameterRoute,
            $parameterDefaults,
            $parameterRegs,
            $parameterLocale
        );

        $this->assertInstanceOf($routerClass, $expectedRouter);
        $this->assertInstanceOf('Zend_Controller_Router_Route_Interface', $expectedRouter);
    }

    /**
     * @test
     * @expectedException \LogicException
     */
    public function testCreateRouteNegative()
    {
        $this->objectManager->expects($this->once())
            ->method('create')
            ->will($this->returnValue(new \StdClass));

        $object = new \Magento\Framework\Controller\Router\Route\Factory($this->objectManager);
        $object->createRoute(
            'routerClass',
            'router'
        );
    }
}

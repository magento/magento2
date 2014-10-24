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

namespace Magento\Framework\App\Route\ConfigInterface;

class ProxyTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\Framework\App\Route\ConfigInterface\Proxy
     */
    protected $_proxy;

    /**
     * @var \Magento\Framework\App\Route\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    public function setUp()
    {
        $this->_object = $this->getMock(
            '\Magento\Framework\App\Route\ConfigInterface',
            ['getRouteFrontName', 'getRouteByFrontName', 'getModulesByFrontName'],
            [],
            '',
            false
        );

        $objectManager = $this->getMock('\Magento\Framework\ObjectManager\ObjectManager', ['get'], [], '', false);
        $objectManager->expects($this->once())
            ->method('get')
            ->with('Magento\Framework\App\Route\ConfigInterface')
            ->will($this->returnValue($this->_object));

        $this->_proxy = new \Magento\Framework\App\Route\ConfigInterface\Proxy(
            $objectManager,
            'Magento\Framework\App\Route\ConfigInterface'
        );
    }

    public function testGetRouteFrontName()
    {
        $routeId = 1;
        $scope = null;
        $this->_object->expects($this->once())->method('getRouteFrontName')->with($routeId, $scope);
        $this->_proxy->getRouteFrontName($routeId, $scope);
    }

    public function testGetRouteByFrontName()
    {
        $frontName = 'route';
        $scope = null;
        $this->_object->expects($this->once())->method('getRouteByFrontName')->with($frontName, $scope);
        $this->_proxy->getRouteByFrontName($frontName, $scope);
    }

    public function testGetModulesByFrontName()
    {
        $frontName = 'route';
        $scope = null;
        $this->_object->expects($this->once())->method('getModulesByFrontName')->with($frontName, $scope);
        $this->_proxy->getModulesByFrontName($frontName, $scope);
    }
}

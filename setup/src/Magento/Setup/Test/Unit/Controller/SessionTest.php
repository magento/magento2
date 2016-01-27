<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\Session;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject| \Magento\Setup\Model\ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    private $serviceManager;

    public function setUp()
    {
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface', [], '', false);
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', ['get'], [], '', false);
        $this->objectManager = $objectManager;
        $this->objectManagerProvider = $objectManagerProvider;

        $this->serviceManager = $this->getMock('Zend\ServiceManager\ServiceManager', ['get'], [], '', false);
    }

    /**
     * @covers \Magento\Setup\Controller\Session::testUnloginAction
     */
    public function testUnloginAction()
    {
        $this->objectManagerProvider->expects($this->once())->method('get')->will(
            $this->returnValue($this->objectManager)
        );
        $deployConfigMock = $this->getMock('Magento\Framework\App\DeploymentConfig', ['isAvailable'], [], '', false);
        $deployConfigMock->expects($this->once())->method('isAvailable')->will($this->returnValue(true));

        $stateMock = $this->getMock('Magento\Framework\App\State', ['setAreaCode'], [], '', false);
        $stateMock->expects($this->once())->method('setAreaCode');

        $sessionConfigMock =
            $this->getMock('Magento\Backend\Model\Session\AdminConfig', ['setCookiePath'], [], '', false);
        $sessionConfigMock->expects($this->once())->method('setCookiePath');

        $returnValueMap = [
            ['Magento\Framework\App\State', $stateMock],
            ['Magento\Backend\Model\Session\AdminConfig', $sessionConfigMock]
        ];

        $this->serviceManager->expects($this->once())->method('get')->will($this->returnValue($deployConfigMock));

        $this->objectManager->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnValueMap($returnValueMap));

        $sessionMock = $this->getMock('Magento\Backend\Model\Auth\Session', ['prolong'], [], '', false);
        $this->objectManager->expects($this->once())
            ->method('create')
            ->will($this->returnValue($sessionMock));
        $controller = new Session($this->serviceManager, $this->objectManagerProvider);
        $controller->prolongAction();
    }

    /**
     * @covers \Magento\Setup\Controller\SystemConfig::indexAction
     */
    public function testIndexAction()
    {
        /** @var $controller Session */
        $controller = new Session($this->serviceManager, $this->objectManagerProvider);
        $viewModel = $controller->unloginAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
    }
}

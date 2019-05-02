<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\Session;

class SessionTest extends \PHPUnit\Framework\TestCase
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
        $objectManager =
            $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class, [], '', false);
        $objectManagerProvider =
            $this->createPartialMock(\Magento\Setup\Model\ObjectManagerProvider::class, ['get']);
        $this->objectManager = $objectManager;
        $this->objectManagerProvider = $objectManagerProvider;
        $this->serviceManager = $this->createPartialMock(\Zend\ServiceManager\ServiceManager::class, ['get']);
    }

    /**
     * @covers \Magento\Setup\Controller\Session::unloginAction
     */
    public function testUnloginAction()
    {
        $this->objectManagerProvider->expects($this->once())->method('get')->will(
            $this->returnValue($this->objectManager)
        );
        $deployConfigMock =
            $this->createPartialMock(\Magento\Framework\App\DeploymentConfig::class, ['isAvailable']);
        $deployConfigMock->expects($this->once())->method('isAvailable')->will($this->returnValue(true));

        $stateMock = $this->createPartialMock(\Magento\Framework\App\State::class, ['setAreaCode']);
        $stateMock->expects($this->once())->method('setAreaCode');

        $sessionConfigMock =
            $this->createPartialMock(\Magento\Backend\Model\Session\AdminConfig::class, ['setCookiePath']);
        $sessionConfigMock->expects($this->once())->method('setCookiePath');
        $urlMock = $this->createMock(\Magento\Backend\Model\Url::class);

        $returnValueMap = [
            [\Magento\Framework\App\State::class, $stateMock],
            [\Magento\Backend\Model\Session\AdminConfig::class, $sessionConfigMock],
            [\Magento\Backend\Model\Url::class, $urlMock]
        ];

        $this->serviceManager->expects($this->once())->method('get')->will($this->returnValue($deployConfigMock));

        $this->objectManager->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnValueMap($returnValueMap));

        $sessionMock = $this->createPartialMock(\Magento\Backend\Model\Auth\Session::class, ['prolong']);
        $this->objectManager->expects($this->once())
            ->method('create')
            ->will($this->returnValue($sessionMock));
        $controller = new Session($this->serviceManager, $this->objectManagerProvider);
        $urlMock->expects($this->once())->method('getBaseUrl');
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
        $this->assertInstanceOf(\Zend\View\Model\ViewModel::class, $viewModel);
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\Index;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;


    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Model\Auth
     */
    private $auth;

    public function setUp()
    {
        $this->objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $this->objectManagerProvider = $this->getMock(
            'Magento\Setup\Model\ObjectManagerProvider',
            ['get'],
            [],
            '',
            false
        );
        $this->deploymentConfig = $this->getMock(
            'Magento\Framework\App\DeploymentConfig',
            [],
            [],
            '',
            false
        );
        $this->appState = $this->getMock(
            'Magento\Framework\App\State',
            [],
            [],
            '',
            false
        );
        $this->auth = $this->getMock(
            'Magento\Backend\Model\Auth',
            [],
            [],
            '',
            false
        );
    }

    public function testIndexActionInstalled()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $this->objectManagerProvider->expects($this->once())->method('get')->willReturn($this->objectManager);
        $this->appState->expects($this->once())->method('setAreaCode');
        $this->auth->expects($this->once())->method('isLoggedIn');
        $this->objectManager->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        ['Magento\Framework\App\State', $this->appState],
                        ['Magento\Backend\Model\Auth', $this->auth]
                    ]
                )
            );
        /** @var $controller Index */
        $controller = new Index($this->objectManagerProvider, $this->deploymentConfig);
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertFalse($viewModel->terminate());
    }

    public function testIndexActionNotInstalled()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(false);
        $this->objectManagerProvider->expects($this->exactly(0))->method('get');
        /** @var $controller Index */
        $controller = new Index($this->objectManagerProvider, $this->deploymentConfig);
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertFalse($viewModel->terminate());
    }
}

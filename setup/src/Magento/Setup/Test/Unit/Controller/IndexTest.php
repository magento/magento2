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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\ApplicationStatus
     */
    private $applicationStatus;

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
        $this->applicationStatus = $this->getMock(
            'Magento\Setup\Model\ApplicationStatus',
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
        $this->applicationStatus->expects($this->once())->method('isApplicationInstalled')->willReturn(true);
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
        $controller = new Index($this->objectManagerProvider, $this->applicationStatus);
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertFalse($viewModel->terminate());
    }

    public function testIndexActionNotInstalled()
    {
        $this->applicationStatus->expects($this->once())->method('isApplicationInstalled')->willReturn(false);
        $this->objectManagerProvider->expects($this->exactly(0))->method('get');
        /** @var $controller Index */
        $controller = new Index($this->objectManagerProvider, $this->applicationStatus);
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertFalse($viewModel->terminate());
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Magento\Backend\Model\Session\AdminConfig;
use Magento\Backend\Model\Url;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Controller\Session;
use Magento\Setup\Model\ObjectManagerProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SessionTest extends TestCase
{
    /**
     * @var MockObject|ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var MockObject|ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * @var ServiceManager
     */
    private $serviceManager;

    protected function setUp(): void
    {
        $objectManager =
            $this->getMockForAbstractClass(ObjectManagerInterface::class, [], '', false);
        $objectManagerProvider =
            $this->createPartialMock(ObjectManagerProvider::class, ['get']);
        $this->objectManager = $objectManager;
        $this->objectManagerProvider = $objectManagerProvider;
        $this->serviceManager = $this->createPartialMock(ServiceManager::class, ['get']);
    }

    /**
     * @covers \Magento\Setup\Controller\Session::unloginAction
     */
    public function testUnloginAction()
    {
        $this->objectManagerProvider->expects($this->once())->method('get')->willReturn(
            $this->objectManager
        );
        $deployConfigMock =
            $this->createPartialMock(DeploymentConfig::class, ['isAvailable']);
        $deployConfigMock->expects($this->once())->method('isAvailable')->willReturn(true);

        $sessionMock = $this->createPartialMock(
            \Magento\Backend\Model\Auth\Session::class,
            ['prolong', 'isSessionExists']
        );
        $sessionMock->expects($this->once())->method('isSessionExists')->willReturn(false);

        $stateMock = $this->createPartialMock(State::class, ['setAreaCode']);
        $stateMock->expects($this->once())->method('setAreaCode');

        $sessionConfigMock =
            $this->createPartialMock(AdminConfig::class, ['setCookiePath']);
        $sessionConfigMock->expects($this->once())->method('setCookiePath');
        $urlMock = $this->createMock(Url::class);

        $returnValueMap = [
            [\Magento\Backend\Model\Auth\Session::class, $sessionMock],
            [State::class, $stateMock],
            [AdminConfig::class, $sessionConfigMock],
            [Url::class, $urlMock]
        ];

        $this->serviceManager->expects($this->once())->method('get')->willReturn($deployConfigMock);

        $this->objectManager->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnMap($returnValueMap);

        $this->objectManager->expects($this->once())
            ->method('create')
            ->willReturn($sessionMock);
        $controller = new Session($this->serviceManager, $this->objectManagerProvider);
        $urlMock->expects($this->once())->method('getBaseUrl');
        $controller->prolongAction();
    }

    /**
     * @covers \Magento\Setup\Controller\SystemConfig::indexAction
     */
    public function testIndexAction()
    {
        /** @var Session $controller */
        $controller = new Session($this->serviceManager, $this->objectManagerProvider);
        $viewModel = $controller->unloginAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
    }

    /**
     * @covers \Magento\Setup\Controller\SystemConfig::prolongAction
     */
    public function testProlongActionWithExistingSession()
    {
        $this->objectManagerProvider->expects($this->once())->method('get')->willReturn(
            $this->objectManager
        );
        $deployConfigMock =
            $this->createPartialMock(DeploymentConfig::class, ['isAvailable']);
        $deployConfigMock->expects($this->once())->method('isAvailable')->willReturn(true);
        $sessionMock = $this->createPartialMock(
            \Magento\Backend\Model\Auth\Session::class,
            ['prolong', 'isSessionExists']
        );
        $sessionMock->expects($this->once())->method('isSessionExists')->willReturn(true);

        $this->serviceManager->expects($this->once())->method('get')->willReturn($deployConfigMock);
        $this->objectManager->expects($this->once())
            ->method('get')
            ->willReturn($sessionMock);
        $controller = new Session($this->serviceManager, $this->objectManagerProvider);
        $this->assertEquals(new JsonModel(['success' => true]), $controller->prolongAction());
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\CreateBackup;

class CreateBackupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\MaintenanceMode|\PHPUnit_Framework_MockObject_MockObject
     */
    private $maintenanceMode;

    /**
     * @var \Magento\Setup\Model\ObjectManagerProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerProvider;

    /**
     * @var \Magento\Setup\Model\WebLogger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $log;

    /**
     * @var \Magento\Setup\Model\BackupRollbackFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupRollback;

    /**
     * Controller
     *
     * @var \Magento\Setup\Controller\CreateBackup
     */
    private $controller;

    public function setUp()
    {
        $this->maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $this->objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $this->backupRollback = $this->getMock(
            'Magento\\Setup\Model\\BackupRollback',
            [],
            [],
            '',
            false
        );
        $objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface', [], [], '', false);
        $objectManager->expects($this->once())->method('create')->willReturn($this->backupRollback);
        $this->objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $this->log = $this->getMock('Magento\Setup\Model\WebLogger', [], [], '', false);
        $this->controller = new CreateBackup($this->objectManagerProvider, $this->maintenanceMode, $this->log);
    }

    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

    public function testCreateAction()
    {
        $this->maintenanceMode->expects($this->once())->method('set');
        $jsonModel = $this->controller->createAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
        $this->assertArrayHasKey('backupFiles', $variables);
        $this->assertEquals([], $variables['backupFiles']);
    }

    public function testCreateActionWithExceptions()
    {
        $this->maintenanceMode->expects($this->once())->method('set')->will(
            $this->throwException(new \Exception)
        );
        $jsonModel = $this->controller->createAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertFalse($variables['success']);
        $this->assertArrayHasKey('error', $variables);
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\TakeBackup;

class TakeBackupTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Framework\Setup\BackupRollbackFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupRollbackFactory;

    /**
     * Controller
     *
     * @var \Magento\Setup\Controller\TakeBackup
     */
    private $controller;

    public function setUp()
    {
        $this->maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $this->objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $this->backupRollbackFactory = $this->getMock(
            'Magento\Framework\Setup\BackupRollbackFactory',
            [],
            [],
            '',
            false
        );
        $objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface', [], [], '', false);
        $objectManager->expects($this->once())->method('get')->willReturn($this->backupRollbackFactory);
        $this->objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $this->log = $this->getMock('Magento\Setup\Model\WebLogger', [], [], '', false);
        $this->controller = new TakeBackup($this->objectManagerProvider, $this->maintenanceMode, $this->log);
    }

    public function testIndexAction()
    {
        $this->maintenanceMode->expects($this->once())->method('set');
        $this->backupRollbackFactory->expects($this->once())->method('create');
        $jsonModel = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
        $this->assertArrayHasKey('backupFiles', $variables);
        $this->assertEquals([], $variables['backupFiles']);
    }

    public function testIndexActionWithExceptions()
    {
        $this->maintenanceMode->expects($this->exactly(2))->method('set');
        $this->backupRollbackFactory->expects($this->once())->method('create')->will(
            $this->throwException(new \Exception)
        );
        $jsonModel = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertFalse($variables['success']);
        $this->assertArrayHasKey('error', $variables);
    }
}

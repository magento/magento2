<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\CompleteBackup;
use \Magento\Setup\Controller\ResponseTypeInterface;

class CompleteBackupTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\Framework\App\MaintenanceMode|\PHPUnit_Framework_MockObject_MockObject
     */
    private $maintenanceMode;

    /**
     * Controller
     *
     * @var \Magento\Setup\Controller\CompleteBackup
     */
    private $controller;

    public function setUp()
    {
        $this->maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $this->controller = new CompleteBackup($this->maintenanceMode);
    }

    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
        $this->assertSame('/magento/setup/complete-backup.phtml', $viewModel->getTemplate());
    }

    public function testProgressAction()
    {
        $viewModel = $this->controller->progressAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
        $this->assertSame('/magento/setup/complete-backup/progress.phtml', $viewModel->getTemplate());
    }

    public function testMaintenanceAction()
    {
        $this->maintenanceMode->expects($this->once())->method('set');
        $jsonModel = $this->controller->maintenanceAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_SUCCESS, $variables['responseType']);
    }

    public function testMaintenanceActionWithExceptions()
    {
        $this->maintenanceMode->expects($this->once())->method('set')->will(
            $this->throwException(new \Exception)
        );
        $jsonModel = $this->controller->maintenanceAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_ERROR, $variables['responseType']);
        $this->assertArrayHasKey('error', $variables);
    }
}

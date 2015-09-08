<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\Maintenance;
use \Magento\Setup\Controller\ResponseTypeInterface;

class MaintenanceTest extends \PHPUnit_Framework_TestCase
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
        $this->controller = new Maintenance($this->maintenanceMode);
    }

    public function testIndexAction()
    {
        $this->maintenanceMode->expects($this->once())->method('set');
        $jsonModel = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_SUCCESS, $variables['responseType']);
    }

    public function testIndexActionWithExceptions()
    {
        $this->maintenanceMode->expects($this->once())->method('set')->will(
            $this->throwException(new \Exception("Test error message"))
        );
        $jsonModel = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_ERROR, $variables['responseType']);
        $this->assertArrayHasKey('error', $variables);
        $this->assertEquals("Test error message", $variables['error']);
    }
}

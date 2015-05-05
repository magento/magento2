<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\DatabaseCheck;
use Magento\Setup\Validator\DbValidator;

class DatabaseCheckTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Installer
     */
    private $installer;

    /**
     * @var DbValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dbValidator;

    /**
     * Controller
     *
     * @var \Magento\Setup\Controller\DatabaseCheck
     */
    private $controller;

    public function setUp()
    {
        $webLogger = $this->getMock('\Magento\Setup\Model\WebLogger', [], [], '', false);
        $installerFactory = $this->getMock('\Magento\Setup\Model\InstallerFactory', [], [], '', false);
        $this->installer = $this->getMock('\Magento\Setup\Model\Installer', [], [], '', false);
        $installerFactory->expects($this->once())->method('create')->with($webLogger)->willReturn(
            $this->installer
        );
        $this->dbValidator = $this->getMock('Magento\Setup\Validator\DbValidator', [], [], '', false);
        $this->controller = new DatabaseCheck($installerFactory, $webLogger, $this->dbValidator);
    }

    public function testIndexAction()
    {
        $this->dbValidator->expects($this->once())->method('checkDatabaseConnection');
        $jsonModel = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
    }

    public function testIndexActionWithError()
    {
        $this->dbValidator->expects($this->once())->method('checkDatabaseConnection')->will(
            $this->throwException(new \Exception)
        );
        $jsonModel = $this->controller->indexAction();
        $this->assertInstanceOf('\Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertArrayHasKey('error', $variables);
        $this->assertFalse($variables['success']);
    }

    public function testIndexActionCheckPrefix()
    {
        $this->dbValidator->expects($this->once())->method('checkDatabaseTablePrefix');
        $this->controller->indexAction();
    }
}

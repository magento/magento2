<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\Connect;

class ConnectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Composer\ComposerInformation
     */
    private $composerInformation;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\ConnectManager
     */
    private $connectManager;

    /**
     * Controller
     *
     * @var \Magento\Setup\Controller\Connect
     */
    private $controller;

    public function setUp()
    {
        $this->composerInformation =
            $this->getMock('Magento\Framework\Composer\ComposerInformation', [], [], '', false);
        $this->connectManager = $this->getMock('Magento\Setup\Model\ConnectManager', [], [], '', false);
        $this->controller = new Connect($this->composerInformation, $this->connectManager);
    }

    /**
     * @covers \Magento\Setup\Controller\Connect::saveAuthJsonAction
     */
    public function testSaveAuthJsonAction()
    {
        $this->connectManager
            ->expects($this->once())
            ->method('checkCredentialsAction')
            ->will($this->returnValue(\Zend_Json::encode(['success' => true])));
        $this->connectManager
            ->expects($this->once())
            ->method('saveAuthJson');
        $jsonModel = $this->controller->saveAuthJsonAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
    }

    /**
     * @covers \Magento\Setup\Controller\Connect::saveAuthJsonAction
     */
    public function testSaveAuthJsonActionWithError()
    {
        $this->connectManager
            ->expects($this->once())
            ->method('checkCredentialsAction')
            ->will($this->throwException(new \Exception));
        $this->composerInformation
            ->expects($this->never())
            ->method('saveAuthJson');
        $jsonModel = $this->controller->saveAuthJsonAction();
        $this->assertInstanceOf('\Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertArrayHasKey('message', $variables);
        $this->assertFalse($variables['success']);
    }

    /**
     * @covers \Magento\Setup\Controller\Connect::checkAuthAction
     */
    public function testCheckAuthAction()
    {
        $this->connectManager
            ->expects($this->once())
            ->method('getAuthJsonData')
            ->will($this->returnValue(['username' => 'test', 'password' => 'test']));
        $this->connectManager
            ->expects($this->once())
            ->method('checkCredentialsAction')
            ->will($this->returnValue(\Zend_Json::encode(['success' => true])));
        $jsonModel = $this->controller->checkAuthAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
    }

    /**
     * @covers \Magento\Setup\Controller\Connect::checkAuthAction
     */
    public function testCheckAuthActionWithError()
    {
        $this->connectManager
            ->expects($this->once())
            ->method('getAuthJsonData')
            ->will($this->throwException(new \Exception));
        $jsonModel = $this->controller->checkAuthAction();
        $this->assertInstanceOf('\Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertArrayHasKey('message', $variables);
        $this->assertFalse($variables['success']);
    }

    /**
     * @covers \Magento\Setup\Controller\Connect::removeAuthAction
     */
    public function testRemoveCredetinalsAction()
    {
        $this->connectManager
            ->expects($this->once())
            ->method('removeCredentials')
            ->will($this->returnValue(true));

        $jsonModel = $this->controller->removeCredentialsAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
    }

    /**
     * @covers \Magento\Setup\Controller\Connect::removeAuthAction
     */
    public function testRemoveCredentialsWithError()
    {
        $this->connectManager
            ->expects($this->once())
            ->method('removeCredentials')
            ->will($this->throwException(new \Exception));
        $jsonModel = $this->controller->removeCredentialsAction();
        $this->assertInstanceOf('\Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertArrayHasKey('message', $variables);
        $this->assertFalse($variables['success']);
    }

    /**
     * @covers \Magento\Setup\Controller\Connect::popupAuthAction
     */
    public function testPopupAuthAction()
    {
        $viewModel = $this->controller->popupAuthAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }
}

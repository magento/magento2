<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\Marketplace;

class MarketplaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\PackagesAuth
     */
    private $packagesAuth;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\PackagesData
     */
    private $packagesData;

    /**
     * Controller
     *
     * @var \Magento\Setup\Controller\Marketplace
     */
    private $controller;

    public function setUp()
    {
        $this->packagesAuth = $this->getMock('Magento\Setup\Model\PackagesAuth', [], [], '', false);
        $this->packagesData = $this->getMock('Magento\Setup\Model\PackagesData', [], [], '', false);
        $this->controller = new Marketplace($this->packagesAuth, $this->packagesData);
    }

    public function testSaveAuthJsonAction()
    {
        $this->packagesAuth
            ->expects($this->once())
            ->method('checkCredentials')
            ->will($this->returnValue(\Zend_Json::encode(['success' => true])));
        $this->packagesAuth
            ->expects($this->once())
            ->method('saveAuthJson')
            ->willReturn(true);
        $jsonModel = $this->controller->saveAuthJsonAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
    }

    public function testSaveAuthJsonActionWithError()
    {
        $this->packagesAuth
            ->expects($this->once())
            ->method('checkCredentials')
            ->will($this->throwException(new \Exception));
        $this->packagesAuth->expects($this->never())->method('saveAuthJson');
        $jsonModel = $this->controller->saveAuthJsonAction();
        $this->assertInstanceOf('\Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertArrayHasKey('message', $variables);
        $this->assertFalse($variables['success']);
    }

    public function testCheckAuthAction()
    {
        $this->packagesAuth
            ->expects($this->once())
            ->method('getAuthJsonData')
            ->will($this->returnValue(['username' => 'test', 'password' => 'test']));
        $this->packagesAuth
            ->expects($this->once())
            ->method('checkCredentials')
            ->will($this->returnValue(\Zend_Json::encode(['success' => true])));
        $jsonModel = $this->controller->checkAuthAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
    }

    public function testCheckAuthActionWithError()
    {
        $this->packagesAuth
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

    public function testRemoveCredentialsAction()
    {
        $this->packagesAuth
            ->expects($this->once())
            ->method('removeCredentials')
            ->will($this->returnValue(true));

        $jsonModel = $this->controller->removeCredentialsAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
    }

    public function testRemoveCredentialsWithError()
    {
        $this->packagesAuth
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

    public function testPopupAuthAction()
    {
        $viewModel = $this->controller->popupAuthAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

    public function testIndexAction()
    {
        $model = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $model);
    }
}

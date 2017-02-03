<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\Marketplace;

class MarketplaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Composer\ComposerInformation
     */
    private $composerInformation;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\MarketplaceManager
     */
    private $marketplaceManager;

    /**
     * Controller
     *
     * @var \Magento\Setup\Controller\Marketplace
     */
    private $controller;

    public function setUp()
    {
        $this->composerInformation =
            $this->getMock('Magento\Framework\Composer\ComposerInformation', [], [], '', false);
        $this->marketplaceManager = $this->getMock('Magento\Setup\Model\MarketplaceManager', [], [], '', false);
        $this->controller = new Marketplace($this->composerInformation, $this->marketplaceManager);
    }

    /**
     * @covers \Magento\Setup\Controller\Marketplace::saveAuthJsonAction
     */
    public function testSaveAuthJsonAction()
    {
        $this->marketplaceManager
            ->expects($this->once())
            ->method('checkCredentialsAction')
            ->will($this->returnValue(\Zend_Json::encode(['success' => true])));
        $this->marketplaceManager
            ->expects($this->once())
            ->method('saveAuthJson')
            ->willReturn(true);
        $jsonModel = $this->controller->saveAuthJsonAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
    }

    /**
     * @covers \Magento\Setup\Controller\Marketplace::saveAuthJsonAction
     */
    public function testSaveAuthJsonActionWithError()
    {
        $this->marketplaceManager
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
     * @covers \Magento\Setup\Controller\Marketplace::checkAuthAction
     */
    public function testCheckAuthAction()
    {
        $this->marketplaceManager
            ->expects($this->once())
            ->method('getAuthJsonData')
            ->will($this->returnValue(['username' => 'test', 'password' => 'test']));
        $this->marketplaceManager
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
     * @covers \Magento\Setup\Controller\Marketplace::checkAuthAction
     */
    public function testCheckAuthActionWithError()
    {
        $this->marketplaceManager
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
     * @covers \Magento\Setup\Controller\Marketplace::removeAuthAction
     */
    public function testRemoveCredetinalsAction()
    {
        $this->marketplaceManager
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
     * @covers \Magento\Setup\Controller\Marketplace::removeAuthAction
     */
    public function testRemoveCredentialsWithError()
    {
        $this->marketplaceManager
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
     * @covers \Magento\Setup\Controller\Marketplace::popupAuthAction
     */
    public function testPopupAuthAction()
    {
        $viewModel = $this->controller->popupAuthAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }
}

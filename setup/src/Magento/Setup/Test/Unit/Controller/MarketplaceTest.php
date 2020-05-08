<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Magento\Setup\Controller\Marketplace;
use Magento\Setup\Model\PackagesAuth;
use Magento\Setup\Model\PackagesData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MarketplaceTest extends TestCase
{
    /**
     * @var MockObject|PackagesAuth
     */
    private $packagesAuth;

    /**
     * @var MockObject|PackagesData
     */
    private $packagesData;

    /**
     * Controller
     *
     * @var Marketplace
     */
    private $controller;

    protected function setUp(): void
    {
        $this->packagesAuth = $this->createMock(PackagesAuth::class);
        $this->packagesData = $this->createMock(PackagesData::class);
        $this->controller = new Marketplace($this->packagesAuth, $this->packagesData);
    }

    public function testSaveAuthJsonAction()
    {
        $this->packagesAuth
            ->expects($this->once())
            ->method('checkCredentials')
            ->willReturn(json_encode(['success' => true]));
        $this->packagesAuth
            ->expects($this->once())
            ->method('saveAuthJson')
            ->willReturn(true);
        $jsonModel = $this->controller->saveAuthJsonAction();
        $this->assertInstanceOf(ViewModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
    }

    public function testSaveAuthJsonActionWithError()
    {
        $this->packagesAuth
            ->expects($this->once())
            ->method('checkCredentials')
            ->willThrowException(new \Exception());
        $this->packagesAuth->expects($this->never())->method('saveAuthJson');
        $jsonModel = $this->controller->saveAuthJsonAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
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
            ->willReturn(['username' => 'test', 'password' => 'test']);
        $this->packagesAuth
            ->expects($this->once())
            ->method('checkCredentials')
            ->willReturn(json_encode(['success' => true]));
        $jsonModel = $this->controller->checkAuthAction();
        $this->assertInstanceOf(ViewModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
    }

    public function testCheckAuthActionWithError()
    {
        $this->packagesAuth
            ->expects($this->once())
            ->method('getAuthJsonData')
            ->willThrowException(new \Exception());
        $jsonModel = $this->controller->checkAuthAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
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
            ->willReturn(true);

        $jsonModel = $this->controller->removeCredentialsAction();
        $this->assertInstanceOf(ViewModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
    }

    public function testRemoveCredentialsWithError()
    {
        $this->packagesAuth
            ->expects($this->once())
            ->method('removeCredentials')
            ->willThrowException(new \Exception());
        $jsonModel = $this->controller->removeCredentialsAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertArrayHasKey('message', $variables);
        $this->assertFalse($variables['success']);
    }

    public function testPopupAuthAction()
    {
        $viewModel = $this->controller->popupAuthAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

    public function testIndexAction()
    {
        $model = $this->controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $model);
    }
}

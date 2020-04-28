<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Magento\Setup\Controller\ResponseTypeInterface;
use Magento\Setup\Controller\SelectVersion;
use Magento\Setup\Model\SystemPackage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SelectVersionTest extends TestCase
{
    /**
     * @var SystemPackage|MockObject
     */
    private $systemPackage;

    /**
     * Controller
     *
     * @var SelectVersion
     */
    private $controller;

    protected function setUp(): void
    {
        $this->systemPackage = $this->createMock(SystemPackage::class);
        $this->controller = new SelectVersion(
            $this->systemPackage
        );
    }

    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

    public function testSystemPackageAction()
    {
        $this->systemPackage->expects($this->once())
            ->method('getPackageVersions')
            ->willReturn([
                'package' => 'magento/product-community-edition',
                'versions' => [
                    'id' => 'magento/product-community-edition',
                    'name' => 'Version 1.0.0'
                ]
            ]);
        $jsonModel = $this->controller->systemPackageAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_SUCCESS, $variables['responseType']);
    }

    public function testSystemPackageActionActionWithError()
    {
        $this->systemPackage->expects($this->once())
            ->method('getPackageVersions')
            ->willThrowException(new \Exception("Test error message"));
        $jsonModel = $this->controller->systemPackageAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_ERROR, $variables['responseType']);
    }

    public function testInstalledSystemPackageAction()
    {
        $this->systemPackage->expects($this->once())
            ->method('getInstalledSystemPackages')
            ->willReturn([
                'package' => 'magento/product-community-edition',
                'versions' => [
                    'id' => 'magento/product-community-edition',
                    'name' => 'Version 1.0.0'
                ]
            ]);
        $jsonModel = $this->controller->installedSystemPackageAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_SUCCESS, $variables['responseType']);
    }

    public function testInstalledSystemPackageActionWithError()
    {
        $this->systemPackage->expects($this->once())
            ->method('getInstalledSystemPackages')
            ->willThrowException(new \Exception("Test error message"));
        $jsonModel = $this->controller->installedSystemPackageAction();
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_ERROR, $variables['responseType']);
    }
}

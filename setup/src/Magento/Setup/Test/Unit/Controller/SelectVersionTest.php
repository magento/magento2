<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\SelectVersion;
use \Magento\Setup\Controller\ResponseTypeInterface;

class SelectVersionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Model\SystemPackage|\PHPUnit_Framework_MockObject_MockObject
     */
    private $systemPackage;

    /**
     * Controller
     *
     * @var \Magento\Setup\Controller\SelectVersion
     */
    private $controller;

    public function setUp()
    {
        $this->systemPackage = $this->createMock(\Magento\Setup\Model\SystemPackage::class);
        $this->controller = new SelectVersion(
            $this->systemPackage
        );
    }

    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf(\Zend\View\Model\ViewModel::class, $viewModel);
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
        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_SUCCESS, $variables['responseType']);
    }

    public function testSystemPackageActionActionWithError()
    {
        $this->systemPackage->expects($this->once())
            ->method('getPackageVersions')
            ->will($this->throwException(new \Exception("Test error message")));
        $jsonModel = $this->controller->systemPackageAction();
        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $jsonModel);
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
        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_SUCCESS, $variables['responseType']);
    }

    public function testInstalledSystemPackageActionWithError()
    {
        $this->systemPackage->expects($this->once())
            ->method('getInstalledSystemPackages')
            ->will($this->throwException(new \Exception("Test error message")));
        $jsonModel = $this->controller->installedSystemPackageAction();
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_ERROR, $variables['responseType']);
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Composer\InfoCommand;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Module\PackageInfo;

class ComponentGridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ComposerInformation
     */
    private $composerInformationMock;

    /**
     * Module package info
     *
     * @var PackageInfo
     */
    private $packageInfo;

    /**
     * @var InfoCommand
     */
    private $infoCommand;

    /**
     * Controller
     *
     * @var ComponentGrid
     */
    private $controller;

    /**
     * @var array
     */
    private $componentData = [];

    /**
     * @var array
     */
    private $lastSyncData = [];

    public function __construct()
    {
        $this->lastSyncData = [
            "lastSyncDate" => "2015/08/10 21:05:34",
            "packages" => [
                'magento/sample-module1' => [
                    'name' => 'magento/sample-module1',
                    'type' => 'magento2-module',
                    'version' => '1.0.0'
                ]
            ]
        ];

        $this->componentData = [
            'magento/sample-module1' => [
                'name' => 'magento/sample-module1',
                'type' => 'magento2-module',
                'version' => '1.0.0'
            ]
        ];
        $this->composerInformationMock = $this->getMock(
            'Magento\Framework\Composer\ComposerInformation',
            [],
            [],
            '',
            false
        );
        $objectManagerProvider = $this->getMock(
            'Magento\Setup\Model\ObjectManagerProvider',
            [],
            [],
            '',
            false
        );
        $objectManager = $this->getMock(
            'Magento\Framework\ObjectManagerInterface',
            [],
            [],
            '',
            false
        );
        $objectManagerProvider->expects($this->once())
            ->method('get')
            ->willReturn($objectManager);
        $packageInfoFactory = $this->getMock(
            'Magento\Framework\Module\PackageInfoFactory',
            [],
            [],
            '',
            false
        );
        $objectManager->expects($this->once())
            ->method('get')
            ->willReturn($packageInfoFactory);
        $this->packageInfo = $this->getMock(
            'Magento\Framework\Module\PackageInfo',
            [],
            [],
            '',
            false
        );
        $packageInfoFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->packageInfo);
        $magentoComposerApplicationFactory = $this->getMock(
            'Magento\Framework\Composer\MagentoComposerApplicationFactory',
            [],
            [],
            '',
            false
        );
        $this->infoCommand = $this->getMock(
            'Magento\Composer\InfoCommand',
            [],
            [],
            '',
            false
        );
        $magentoComposerApplicationFactory->expects($this->once())
            ->method('createInfoCommand')
            ->willReturn($this->infoCommand);
        $this->controller = new ComponentGrid(
            $this->composerInformationMock,
            $objectManagerProvider,
            $magentoComposerApplicationFactory
        );
    }

    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

    public function testComponentsAction()
    {
        $this->infoCommand->expects($this->once())
            ->method('run')
            ->willReturn([
                'versions' => '3.0.0, 2.0.0',
                'current_version' => '1.0.0',
                'new_versions' => [
                    '3.0.0',
                    '2.0.0'
                ]
            ]);
        $this->packageInfo->expects($this->once())
            ->method('getModuleName')
            ->willReturn('Sample_Module');
        $this->composerInformationMock->expects($this->once())
            ->method('getInstalledMagentoPackages')
            ->willReturn($this->componentData);
        $this->composerInformationMock->expects($this->once())
            ->method('isPackageInComposerJson')
            ->willReturn(true);
        $this->composerInformationMock->expects($this->once())
            ->method('getPackagesForUpdate')
            ->willReturn($this->lastSyncData);
        $jsonModel = $this->controller->componentsAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
        $expected = [[
            'name' => 'magento/sample-module1',
            'type' => 'magento2-module',
            'version' => '1.0.0',
            'vendor' => 'magento',
            'moduleName' => 'Sample_Module',
            'update' => true,
            'uninstall' => true
        ]];
        $this->assertEquals($expected, $variables['components']);
        $this->assertArrayHasKey('total', $variables);
        $this->assertEquals(1, $variables['total']);
        $this->assertEquals($this->lastSyncData, $variables['lastSyncData']);
    }

    public function testSyncAction()
    {
        $this->composerInformationMock->expects($this->once())
            ->method('syncPackagesForUpdate');
        $this->composerInformationMock->expects($this->once())
            ->method('getPackagesForUpdate')
            ->willReturn($this->lastSyncData);
        $jsonModel = $this->controller->syncAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
        $this->assertEquals($this->lastSyncData, $variables['lastSyncData']);
    }
}

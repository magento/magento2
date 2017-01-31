<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Setup\Controller\ComponentGrid;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleList;
use Magento\Setup\Model\PackagesAuth;
use Magento\Setup\Model\PackagesData;

class ComponentGridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ComposerInformation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $composerInformationMock;

    /**
     * @var FullModuleList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fullModuleListMock;

    /**
     * @var ModuleList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $enabledModuleListMock;

    /**
     * @var PackageInfoFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $packageInfoFactoryMock;

    /**
     * Module package info
     *
     * @var PackageInfo
     */
    private $packageInfo;

    /**
     * Controller
     *
     * @var ComponentGrid
     */
    private $controller;

    /**
     * @var PackagesData|\PHPUnit_Framework_MockObject_MockObject
     */
    private $packagesData;

    /**
     * @var PackagesAuth|\PHPUnit_Framework_MockObject_MockObject
     */
    private $packagesAuth;
    
    /**
     * @var ObjectManagerProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerProvider;
    
    /**
     * @var array
     */
    private $componentData = [];

    /**
     * @var array
     */
    private $lastSyncData = [];

    /**#@+
     * Canned formatted date and time to return from mock
     */
    const FORMATTED_DATE = 'Jan 15, 1980';
    const FORMATTED_TIME = '1:55:55 PM';
    /**#@-*/

    public function setUp()
    {
        $this->lastSyncData = [
            "lastSyncDate" => [
                'date' => self::FORMATTED_DATE,
                'time' => self::FORMATTED_TIME,
            ],
            "packages" => [
                'magento/sample-module-one' => [
                    'name' => 'magento/sample-module-one',
                    'type' => 'magento2-module',
                    'version' => '1.0.0'
                ]
            ],
            'countOfInstall' => 0,
            'countOfUpdate' => 1
        ];
        $this->componentData = [
            'magento/sample-module-one' => [
                'name' => 'magento/sample-module-one',
                'type' => 'magento2-module',
                'version' => '1.0.0'
            ]
        ];
        $allComponentData = [
            'magento/sample-module-two' => [
                'name' => 'magento/sample-module-two',
                'type' => 'magento2-module',
                'version' => '1.0.0'
            ]
        ];
        $allComponentData = array_merge($allComponentData, $this->componentData);
        $this->composerInformationMock = $this->getMock(
            'Magento\Framework\Composer\ComposerInformation',
            [],
            [],
            '',
            false
        );
        /** @var ObjectManagerProvider|\PHPUnit_Framework_MockObject_MockObject $objectManagerProvider */
        $this->objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $this->packageInfoFactoryMock = $this
            ->getMock('Magento\Framework\Module\PackageInfoFactory', [], [], '', false);
        $this->enabledModuleListMock = $this->getMock('Magento\Framework\Module\ModuleList', [], [], '', false);
        $this->enabledModuleListMock->expects($this->any())->method('has')->willReturn(true);
        $this->fullModuleListMock = $this->getMock('Magento\Framework\Module\FullModuleList', [], [], '', false);
        $this->fullModuleListMock->expects($this->any())->method('getNames')->willReturn($allComponentData);
        
        $this->packageInfo = $this->getMock('Magento\Framework\Module\PackageInfo', [], [], '', false);
        $this->packagesData = $this->getMock('Magento\Setup\Model\PackagesData', [], [], '', false);
        $this->packagesAuth = $this->getMock('Magento\Setup\Model\PackagesAuth', [], [], '', false);

        $this->controller = new ComponentGrid(
            $this->composerInformationMock,
            $this->objectManagerProvider,
            $this->packagesData,
            $this->packagesAuth
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
        $objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface', [], [], '', false);
        $this->objectManagerProvider->expects($this->once())
            ->method('get')
            ->willReturn($objectManager);
        $objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap([
                ['Magento\Framework\Module\PackageInfoFactory', $this->packageInfoFactoryMock],
                ['Magento\Framework\Module\FullModuleList', $this->fullModuleListMock],
                ['Magento\Framework\Module\ModuleList', $this->enabledModuleListMock],
            ]);
        $this->packageInfoFactoryMock->expects($this->once())->method('create')->willReturn($this->packageInfo);
        $this->fullModuleListMock->expects($this->once())
            ->method('getNames')
            ->willReturn(['magento/sample-module1']);
        $this->packageInfo->expects($this->once())
            ->method('getModuleName')
            ->willReturn('Sample_Module');
        $this->packageInfo->expects($this->exactly(2))
            ->method('getPackageName')
            ->willReturn($this->componentData['magento/sample-module-one']['name']);
        $this->packageInfo->expects($this->exactly(2))
            ->method('getVersion')
            ->willReturn($this->componentData['magento/sample-module-one']['version']);
        $this->enabledModuleListMock->expects($this->once())
            ->method('has')
            ->willReturn(true);
        $this->composerInformationMock->expects($this->once())
            ->method('getInstalledMagentoPackages')
            ->willReturn($this->componentData);
        $this->composerInformationMock->expects($this->once())
            ->method('isPackageInComposerJson')
            ->willReturn(true);
        $this->packagesAuth->expects($this->once())->method('getAuthJsonData')->willReturn([
            'username' => 'someusername', 'password' => 'somepassword'
        ]);
        $this->packagesData->expects($this->once())
            ->method('syncPackagesData')
            ->willReturn($this->lastSyncData);
        $jsonModel = $this->controller->componentsAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
        $expected = [[
            'name' => 'magento/sample-module-one',
            'type' => 'magento2-module',
            'version' => '1.0.0',
            'update' => false,
            'uninstall' => true,
            'vendor' => 'magento',
            'moduleName' => 'Sample_Module',
            'enable' => true,
            'disable' => false
        ]];
        $this->assertEquals($expected, $variables['components']);
        $this->assertArrayHasKey('total', $variables);
        $this->assertEquals(1, $variables['total']);
        $this->assertEquals($this->lastSyncData, $variables['lastSyncData']);
    }

    public function testSyncAction()
    {
        $this->packagesData->expects($this->once())
            ->method('syncPackagesData')
            ->willReturn($this->lastSyncData);
        $jsonModel = $this->controller->syncAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
        $this->assertEquals($this->lastSyncData, $variables['lastSyncData']);
    }
}

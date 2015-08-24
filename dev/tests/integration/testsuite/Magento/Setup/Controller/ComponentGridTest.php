<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Module\FullModuleList;

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
     * enabled module list
     *
     * @var ModuleList
     */
    private $moduleList;

    /**
     * all modules
     *
     * @var FullModuleList
     */
    private $fullModuleList;

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
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);

        $packageInfoFactory = $this->getMock('Magento\Framework\Module\PackageInfoFactory', [], [], '', false);
        $this->moduleList = $this->getMock('Magento\Framework\Module\ModuleList', [], [], '', false);
        $this->fullModuleList = $this->getMock('Magento\Framework\Module\FullModuleList', [], [], '', false);
        $objectValueMap = [
            ['Magento\Framework\Module\PackageInfoFactory', $packageInfoFactory],
            ['Magento\Framework\Module\ModuleList', $this->moduleList],
            ['Magento\Framework\Module\FullModuleList', $this->fullModuleList]
        ];

        $objectManager->expects($this->exactly(3))->method('get')->will($this->returnValueMap($objectValueMap));

        $this->packageInfo = $this->getMock('Magento\Framework\Module\PackageInfo', [], [], '', false);
        $packageInfoFactory->expects($this->once())->method('create')->willReturn($this->packageInfo);
        $this->controller = new ComponentGrid($this->composerInformationMock, $objectManagerProvider);
    }

    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

    public function testComponentsAction()
    {
        $this->composerInformationMock->expects($this->once())
            ->method('getPackagesForUpdate')
            ->willReturn($this->lastSyncData);
        $this->composerInformationMock->expects($this->once())
            ->method('getInstalledMagentoPackages')
            ->willReturn($this->componentData);
        $this->fullModuleList->expects($this->once())
            ->method('getNames')
            ->willReturn(['Sample_Module_From_Filesystem_One', 'Sample_Module_From_Filesystem_Two']);
        $objectValueMap = [
            ['Sample_Module_From_Filesystem_One', 'magento/ModuleFromFilesystemOne'],
            ['Sample_Module_From_Filesystem_Two', 'magento/ModuleFromFilesystemTwo'],
        ];
        $this->packageInfo->expects($this->exactly(2))
            ->method('getPackageName')
            ->will($this->returnValueMap($objectValueMap));
        $objectValueMap = [
            ['Sample_Module_From_Filesystem_One', '1.0.0'],
            ['Sample_Module_From_Filesystem_Two', '2.0.0'],
        ];
        $this->packageInfo->expects($this->exactly(2))
            ->method('getVersion')
            ->will($this->returnValueMap($objectValueMap));
        $objectValueMap = [
            ['magento/ModuleFromFilesystemOne', 'Sample_Module_From_Filesystem_One'],
            ['magento/ModuleFromFilesystemTwo', 'Sample_Module_From_Filesystem_Two'],
            ['magento/sample-module1', 'Sample_Module'],
        ];
        $this->packageInfo->expects($this->exactly(3))
            ->method('getModuleName')
            ->will($this->returnValueMap($objectValueMap));

        $objectValueMap = [
            ['magento/ModuleFromFilesystemOne', false],
            ['magento/ModuleFromFilesystemTwo', false],
            ['magento/sample-module1', true],
        ];
        $this->composerInformationMock->expects($this->exactly(3))
            ->method('isPackageInComposerJson')
            ->will($this->returnValueMap($objectValueMap));

        $objectValueMap = [
            ['Sample_Module_From_Filesystem_One', true],
            ['Sample_Module_From_Filesystem_Two', false],
            ['Sample_Module', true],
        ];
        $this->moduleList->expects($this->exactly(3))
            ->method('has')
            ->will($this->returnValueMap($objectValueMap));

        $jsonModel = $this->controller->componentsAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
        $expected = [
            [
                'name' => 'magento/sample-module1',
                'type' => 'magento2-module',
                'version' => '1.0.0',
                'update' => false,
                'uninstall' => true,
                'moduleName' => 'Sample_Module',
                'enable'   => true,
                'disable'   => false,
                'vendor' => 'magento',
            ],
            [
                'name' => 'magento/ModuleFromFilesystemOne',
                'type' => 'magento2-module',
                'version' => '1.0.0',
                'update' => false,
                'uninstall' => false,
                'moduleName' => 'Sample_Module_From_Filesystem_One',
                'enable'   => true,
                'disable'   => false,
                'vendor' => 'magento',
            ],
            [
                'name' => 'magento/ModuleFromFilesystemTwo',
                'type' => 'magento2-module',
                'version' => '2.0.0',
                'update' => false,
                'uninstall' => false,
                'moduleName' => 'Sample_Module_From_Filesystem_Two',
                'enable'   => false,
                'disable'   => true,
                'vendor' => 'magento',
            ],
        ];
        $this->assertEquals($expected, $variables['components']);
        $this->assertArrayHasKey('total', $variables);
        $this->assertEquals(3, $variables['total']);
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

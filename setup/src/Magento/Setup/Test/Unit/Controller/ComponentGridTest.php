<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Setup\Controller\ComponentGrid;
<<<<<<< HEAD
use Magento\Setup\Model\DateTime\TimezoneProvider;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\UpdatePackagesCache;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Setup\Model\MarketplaceManager;
=======
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleList;
use Magento\Setup\Model\PackagesAuth;
use Magento\Setup\Model\PackagesData;
>>>>>>> develop

class ComponentGridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ComposerInformation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $composerInformationMock;

    /**
<<<<<<< HEAD
     * @var UpdatePackagesCache|\PHPUnit_Framework_MockObject_MockObject
     */
    private $updatePackagesCacheMock;

    /**
     * @var TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     *
     */
    private $timezoneMock;

    /**
=======
>>>>>>> develop
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
<<<<<<< HEAD
     * @var MarketplaceManager
     */
    private $marketplaceManagerMock;

    /**
     * @var array
     */
    private $componentData = [];

    /**
     * @var array
     */
    private $lastSyncData = [];
=======
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
>>>>>>> develop

    /**
     * @var array
     */
<<<<<<< HEAD
    private $convertedLastSyncDate = [];
=======
    private $lastSyncData = [];
>>>>>>> develop

    /**#@+
     * Canned formatted date and time to return from mock
     */
    const FORMATTED_DATE = 'Jan 15, 1980';
    const FORMATTED_TIME = '1:55:55 PM';
    /**#@-*/

    public function setUp()
    {
<<<<<<< HEAD
        $this->convertedLastSyncDate = [
            'lastSyncDate' => [
                'date' => self::FORMATTED_DATE,
                'time' => self::FORMATTED_TIME,
            ],
        ];

        $this->lastSyncData = [
            "lastSyncDate" => "1447271496",
=======
        $this->lastSyncData = [
            "lastSyncDate" => [
                'date' => self::FORMATTED_DATE,
                'time' => self::FORMATTED_TIME,
            ],
>>>>>>> develop
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
<<<<<<< HEAD
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface', [], [], '', false);
        $objectManagerProvider->expects($this->once())
            ->method('get')
            ->willReturn($objectManager);
=======
        $this->objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
>>>>>>> develop
        $this->packageInfoFactoryMock = $this
            ->getMock('Magento\Framework\Module\PackageInfoFactory', [], [], '', false);
        $this->enabledModuleListMock = $this->getMock('Magento\Framework\Module\ModuleList', [], [], '', false);
        $this->enabledModuleListMock->expects($this->any())->method('has')->willReturn(true);
        $this->fullModuleListMock = $this->getMock('Magento\Framework\Module\FullModuleList', [], [], '', false);
        $this->fullModuleListMock->expects($this->any())->method('getNames')->willReturn($allComponentData);
<<<<<<< HEAD
        $this->timezoneMock = $this->getMock('Magento\Framework\Stdlib\DateTime\TimezoneInterface', [], [], '', false);
        $objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap([
                ['Magento\Framework\Module\PackageInfoFactory', $this->packageInfoFactoryMock],
                ['Magento\Framework\Module\FullModuleList', $this->fullModuleListMock],
                ['Magento\Framework\Module\ModuleList', $this->enabledModuleListMock],
                ['Magento\Framework\Stdlib\DateTime\TimezoneInterface', $this->timezoneMock]
            ]);

        /** @var TimezoneProvider|\PHPUnit_Framework_MockObject_MockObject $timezoneProviderMock */
        $timezoneProviderMock = $this->getMock('\Magento\Setup\Model\DateTime\TimezoneProvider', [], [], '', false);
        $timezoneProviderMock->expects($this->any())
            ->method('get')
            ->willReturn($this->timezoneMock);
        $this->packageInfo = $this->getMock('Magento\Framework\Module\PackageInfo', [], [], '', false);
        $this->updatePackagesCacheMock = $this->getMock('Magento\Setup\Model\UpdatePackagesCache', [], [], '', false);
        $this->marketplaceManagerMock = $this->getMock('Magento\Setup\Model\MarketplaceManager', [], [], '', false);
        $this->packageInfoFactoryMock->expects($this->once())->method('create')->willReturn($this->packageInfo);
        $this->controller = new ComponentGrid(
            $this->composerInformationMock,
            $objectManagerProvider,
            $this->updatePackagesCacheMock,
            $this->marketplaceManagerMock
        );
        $this->controller->setTimezone($timezoneProviderMock->get());
=======
        
        $this->packageInfo = $this->getMock('Magento\Framework\Module\PackageInfo', [], [], '', false);
        $this->packagesData = $this->getMock('Magento\Setup\Model\PackagesData', [], [], '', false);
        $this->packagesAuth = $this->getMock('Magento\Setup\Model\PackagesAuth', [], [], '', false);

        $this->controller = new ComponentGrid(
            $this->composerInformationMock,
            $this->objectManagerProvider,
            $this->packagesData,
            $this->packagesAuth
        );
>>>>>>> develop
    }

    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

    public function testComponentsAction()
    {
<<<<<<< HEAD
=======
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
>>>>>>> develop
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
<<<<<<< HEAD
        $this->updatePackagesCacheMock->expects($this->once())
            ->method('getPackagesForUpdate')
            ->willReturn($this->lastSyncData);
        $this->setupTimezoneMock();
=======
        $this->packagesAuth->expects($this->once())->method('getAuthJsonData')->willReturn([
            'username' => 'someusername', 'password' => 'somepassword'
        ]);
        $this->packagesData->expects($this->once())
            ->method('syncPackagesData')
            ->willReturn($this->lastSyncData);
>>>>>>> develop
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
<<<<<<< HEAD
        $expectedLastSyncData = array_replace($this->lastSyncData, $this->convertedLastSyncDate);
        $this->assertEquals($expectedLastSyncData, $variables['lastSyncData']);
=======
        $this->assertEquals($this->lastSyncData, $variables['lastSyncData']);
>>>>>>> develop
    }

    public function testSyncAction()
    {
<<<<<<< HEAD
        $this->updatePackagesCacheMock->expects($this->once())
            ->method('syncPackagesForUpdate');
        $this->updatePackagesCacheMock->expects($this->once())
            ->method('getPackagesForUpdate')
            ->willReturn($this->lastSyncData);
        $this->setupTimezoneMock();
=======
        $this->packagesData->expects($this->once())
            ->method('syncPackagesData')
            ->willReturn($this->lastSyncData);
>>>>>>> develop
        $jsonModel = $this->controller->syncAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
<<<<<<< HEAD
        $expectedLastSyncData = array_replace($this->lastSyncData, $this->convertedLastSyncDate);
        $this->assertEquals($expectedLastSyncData, $variables['lastSyncData']);
    }

    /**
     * Prepare the timezone mock to expect calls and return formatted date and time
     *
     * @return none
     */
    private function setupTimezoneMock()
    {
        $this->timezoneMock->expects($this->at(0))
            ->method('formatDateTime')
            ->with(
                $this->isInstanceOf('\DateTime'),
                \IntlDateFormatter::MEDIUM,
                \IntlDateFormatter::NONE,
                null,
                null,
                null
            )->willReturn(self::FORMATTED_DATE);
        $this->timezoneMock->expects($this->at(1))
            ->method('formatDateTime')
            ->with(
                $this->isInstanceOf('\DateTime'),
                \IntlDateFormatter::NONE,
                \IntlDateFormatter::MEDIUM,
                null,
                null,
                null
            )->willReturn(self::FORMATTED_TIME);
=======
        $this->assertEquals($this->lastSyncData, $variables['lastSyncData']);
>>>>>>> develop
    }
}

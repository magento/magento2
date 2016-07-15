<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use Magento\Framework\Module\ModuleList;
use Magento\Setup\Controller\ExtensionGrid;
use Magento\Setup\Model\Grid\Extension;
use Magento\Setup\Model\PackagesAuth;
use Magento\Setup\Model\PackagesData;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ExtensionGridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Extension|MockObject
     */
    private $gridExtension;

    /**
     * Controller
     *
     * @var ExtensionGrid
     */
    private $controller;

    /**
     * @var PackagesData|MockObject
     */
    private $packagesData;

    /**
     * @var PackagesAuth|MockObject
     */
    private $packagesAuth;

    /**
     * @var array
     */
    private $extensionData = [];

    /**
     * @var array
     */
    private $lastSyncData = [];

    /**#@+
     * Formatted date and time to return from mock
     */
    const FORMATTED_DATE = 'Jan 15 1980';
    const FORMATTED_TIME = '01:55PM';
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
        $this->extensionData = [
            [
                'name' => 'magento/sample-module-one',
                'type' => 'magento2-module',
                'version' => '1.0.0',
                'update' => false,
                'uninstall' => true,
                'vendor' => 'magento',
            ]
        ];


        $this->packagesData = $this->getMock(PackagesData::class, [], [], '', false);
        $this->packagesAuth = $this->getMock(PackagesAuth::class, [], [], '', false);
        $this->gridExtension = $this->getMock(Extension::class, [], [], '', false);

        $this->controller = new ExtensionGrid(
            $this->packagesData,
            $this->packagesAuth,
            $this->gridExtension
        );
    }

    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

    public function testExtensionsAction()
    {
        $this->gridExtension->expects($this->once())
            ->method('getList')
            ->willReturn($this->extensionData);

        $this->packagesData->expects($this->once())
            ->method('syncPackagesData')
            ->willReturn($this->lastSyncData);

        $this->packagesAuth->expects($this->once())
             ->method('getAuthJsonData')
             ->willReturn(
            [
                'username' => 'someusername',
                'password' => 'somepassword'
            ]
        );

        $jsonModel = $this->controller->extensionsAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
        $this->assertEquals($this->extensionData, $variables['extensions']);
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

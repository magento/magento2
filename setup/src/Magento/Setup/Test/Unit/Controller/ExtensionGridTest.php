<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Controller;

use Magento\Setup\Controller\ExtensionGrid;
use Magento\Setup\Model\Grid\Extension;
use Magento\Setup\Model\PackagesAuth;
use Magento\Setup\Model\PackagesData;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class ExtensionGridTest
 */
class ExtensionGridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Extension|MockObject
     */
    private $gridExtensionMock;

    /**
     * Controller
     *
     * @var ExtensionGrid
     */
    private $controller;

    /**
     * @var PackagesData|MockObject
     */
    private $packagesDataMock;

    /**
     * @var PackagesAuth|MockObject
     */
    private $packagesAuthMock;

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

        $this->packagesDataMock = $this->getMock(PackagesData::class, [], [], '', false);
        $this->packagesAuthMock = $this->getMock(PackagesAuth::class, [], [], '', false);
        $this->gridExtensionMock = $this->getMock(Extension::class, [], [], '', false);

        $this->controller = new ExtensionGrid(
            $this->packagesDataMock,
            $this->packagesAuthMock,
            $this->gridExtensionMock
        );
    }

    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf(\Zend\View\Model\ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

    public function testExtensionsAction()
    {
        $this->gridExtensionMock->expects($this->once())
            ->method('getList')
            ->willReturn($this->extensionData);
        $this->packagesDataMock->expects($this->once())
            ->method('syncPackagesData')
            ->willReturn($this->lastSyncData);
        $this->packagesAuthMock->expects($this->once())
             ->method('getAuthJsonData')
             ->willReturn(
                 [
                     'username' => 'someusername',
                     'password' => 'somepassword'
                 ]
             );

        $jsonModel = $this->controller->extensionsAction();
        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $jsonModel);
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
        $authDataJson = ['username' => 'admin', 'password' => '12345'];

        $this->packagesDataMock->expects($this->once())
            ->method('syncPackagesData')
            ->willReturn($this->lastSyncData);
        $this->packagesAuthMock->expects($this->once())
            ->method('getAuthJsonData')
            ->willReturn($authDataJson);
        $this->packagesAuthMock->expects($this->once())
            ->method('checkCredentials')
            ->with(
                $authDataJson['username'],
                $authDataJson['password']
            );

        $jsonModel = $this->controller->syncAction();
        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
        $this->assertEquals($this->lastSyncData, $variables['lastSyncData']);
    }
}

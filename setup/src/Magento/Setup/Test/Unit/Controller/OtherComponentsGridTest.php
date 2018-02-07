<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\OtherComponentsGrid;
use \Magento\Setup\Controller\ResponseTypeInterface;
use Magento\Composer\InfoCommand;

class OtherComponentsGridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Composer\ComposerInformation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $composerInformation;

    /**
     * @var \Magento\Composer\InfoCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    private $infoCommand;

    /**
     * Controller
     *
     * @var \Magento\Setup\Controller\OtherComponentsGrid
     */
    private $controller;

    public function setUp()
    {
        $this->composerInformation = $this->getMock(
            'Magento\Framework\Composer\ComposerInformation',
            [],
            [],
            '',
            false
        );
        $this->infoCommand = $this->getMock('Magento\Composer\InfoCommand', [], [], '', false);
        $magentoComposerApplicationFactory = $this->getMock(
            'Magento\Framework\Composer\MagentoComposerApplicationFactory',
            [],
            [],
            '',
            false
        );
        $magentoComposerApplicationFactory->expects($this->once())
            ->method('createInfoCommand')
            ->willReturn($this->infoCommand);
        $this->controller = new OtherComponentsGrid(
            $this->composerInformation,
            $magentoComposerApplicationFactory
        );
    }

    public function testComponentsAction()
    {
        $this->composerInformation->expects($this->once())
            ->method('getInstalledMagentoPackages')
            ->willReturn([
                'magento/sample-module1' => [
                    'name' => 'magento/sample-module1',
                    'type' => 'magento2-module',
                    'version' => '1.0.0'
                ]
            ]);
        $this->composerInformation->expects($this->once())
            ->method('isPackageInComposerJson')
            ->willReturn(true);
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
        $jsonModel = $this->controller->componentsAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_SUCCESS, $variables['responseType']);
        $this->assertArrayHasKey('components', $variables);
        $expected = [
            '0' => [
                'name' => 'magento/sample-module1',
                'type' => 'magento2-module',
                'version' => '1.0.0',
                'vendor' => 'magento',
                'updates' => [
                    [
                        'id' => '3.0.0',
                        'name' => '3.0.0 (latest)'
                    ],
                    [
                        'id' => '2.0.0',
                        'name' => '2.0.0'
                    ],
                    [
                        'id' => '1.0.0',
                        'name' => '1.0.0 (current)'
                    ]
                ],
                'dropdownId' => 'dd_magento/sample-module1',
                'checkboxId' => 'cb_magento/sample-module1'
            ]
        ];
        $this->assertEquals($expected, $variables['components']);
        $this->assertArrayHasKey('total', $variables);
        $this->assertEquals(1, $variables['total']);
    }

    public function testComponentsActionWithError()
    {
        $this->composerInformation->expects($this->once())
            ->method('getInstalledMagentoPackages')
            ->will($this->throwException(new \Exception("Test error message")));
        $jsonModel = $this->controller->componentsAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_ERROR, $variables['responseType']);
    }

    public function testIndexAction()
    {
        $model = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $model);
    }
}

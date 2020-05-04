<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Magento\Composer\InfoCommand;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Composer\MagentoComposerApplicationFactory;
use Magento\Setup\Controller\OtherComponentsGrid;
use Magento\Setup\Controller\ResponseTypeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OtherComponentsGridTest extends TestCase
{
    /**
     * @var ComposerInformation|MockObject
     */
    private $composerInformation;

    /**
     * @var InfoCommand|MockObject
     */
    private $infoCommand;

    /**
     * Controller
     *
     * @var OtherComponentsGrid
     */
    private $controller;

    protected function setUp(): void
    {
        $this->composerInformation = $this->createMock(ComposerInformation::class);
        $this->infoCommand = $this->createMock(InfoCommand::class);
        $magentoComposerApplicationFactory =
            $this->createMock(MagentoComposerApplicationFactory::class);
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
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
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
            ->willThrowException(new \Exception("Test error message"));
        $jsonModel = $this->controller->componentsAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_ERROR, $variables['responseType']);
    }

    public function testIndexAction()
    {
        $model = $this->controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $model);
    }
}

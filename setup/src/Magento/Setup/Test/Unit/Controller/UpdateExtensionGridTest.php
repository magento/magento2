<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Magento\Setup\Controller\UpdateExtensionGrid;
use Magento\Setup\Model\Grid\Extension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * CTest for \Magento\Setup\Controller\UpdateExtensionGrid
 */
class UpdateExtensionGridTest extends TestCase
{
    /**
     * @var Extension|MockObject
     */
    private $gridExtensionMock;

    /**
     * Controller
     *
     * @var UpdateExtensionGrid
     */
    private $controller;

    protected function setUp(): void
    {
        $this->gridExtensionMock = $this->createMock(Extension::class);

        $this->controller = new UpdateExtensionGrid(
            $this->gridExtensionMock
        );
    }

    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();

        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

    public function testExtensionsAction()
    {
        $extensionData = [
            [
                'name' => 'magento-package-1',
                'product_name' => 'magento/package-1',
                'type' => 'magento2-module',
                'version' => '1.0.0',
                'latestVersion' => '2.0.5',
                'versions' => ['2.0.5', '2.0.4', '2.0.3'],
                'update' => true,
                'uninstall' => true
            ]
        ];
        $this->gridExtensionMock->expects($this->once())
            ->method('getListForUpdate')
            ->willReturn($extensionData);

        $jsonModel = $this->controller->extensionsAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();

        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
        $this->assertEquals($extensionData, $variables['extensions']);
        $this->assertArrayHasKey('total', $variables);
        $this->assertEquals(1, $variables['total']);
    }
}

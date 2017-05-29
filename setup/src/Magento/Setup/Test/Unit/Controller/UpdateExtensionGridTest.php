<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Controller;

use Magento\Setup\Controller\UpdateExtensionGrid;
use Magento\Setup\Model\Grid\Extension;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Class UpdateExtensionGridTest
 */
class UpdateExtensionGridTest extends \PHPUnit_Framework_TestCase
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

    public function setUp()
    {
        $this->gridExtensionMock = $this->getMock(Extension::class, [], [], '', false);

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

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Magento\Setup\Controller\ModuleGrid;
use Magento\Setup\Model\Grid\Module;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Setup\Controller\ModuleGrid
 */
class ModuleGridTest extends TestCase
{
    /**
     * @var Module|MockObject
     */
    private $gridModuleMock;

    /**
     * Controller
     *
     * @var ModuleGrid
     */
    private $controller;

    protected function setUp(): void
    {
        $this->gridModuleMock = $this->getMockBuilder(Module::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = new ModuleGrid(
            $this->gridModuleMock
        );
    }

    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

    public function testModulesAction()
    {
        $moduleList = [
            [
                'name' => 'magento/sample-module-one',
                'type' => 'Module',
                'version' => '1.0.0',
                'vendor' => 'magento',
                'moduleName' => 'Sample_Module_One',
                'enable' => true,
                'requiredBy' => []
            ],
            [
                'name' => 'magento/sample-module-two',
                'type' => 'Module',
                'version' => '1.0.0',
                'vendor' => 'magento',
                'moduleName' => 'Sample_Module_Two',
                'enable' => true,
                'requiredBy' => []
            ]
        ];

        $this->gridModuleMock->expects(static::once())
            ->method('getList')
            ->willReturn($moduleList);

        $jsonModel = $this->controller->modulesAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
        $this->assertEquals($moduleList, $variables['modules']);
        $this->assertArrayHasKey('total', $variables);
        $this->assertEquals(2, $variables['total']);
    }
}

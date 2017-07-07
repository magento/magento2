<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use Magento\Setup\Controller\ModuleGrid;
use Magento\Setup\Model\Grid\Module;

/**
 * Class ModuleGridTest
 */
class ModuleGridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Module|\PHPUnit_Framework_MockObject_MockObject
     */
    private $gridModuleMock;

    /**
     * Controller
     *
     * @var ModuleGrid
     */
    private $controller;

    public function setUp()
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
        $this->assertInstanceOf(\Zend\View\Model\ViewModel::class, $viewModel);
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
        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
        $this->assertEquals($moduleList, $variables['modules']);
        $this->assertArrayHasKey('total', $variables);
        $this->assertEquals(2, $variables['total']);
    }
}

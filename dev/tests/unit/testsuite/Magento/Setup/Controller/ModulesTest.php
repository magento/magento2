<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

class ModulesTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    private $status;


    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\ModuleStatus
     */
    private $modules;

    /**
     * Controller
     *
     * @var \Magento\Setup\Controller\ModuleCheck
     */
    private $controller;

    public function setUp()
    {
        $this->objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $this->status = $this->getMock('Magento\Framework\Module\Status', [], [], '', false);
        $objectManagerFactory = $this->getMock('Magento\Setup\Model\ObjectManagerFactory', [], [], '', false);
        $objectManagerFactory->expects($this->once())->method('create')->willReturn($this->objectManager);
        $this->modules = $this->getMock('\Magento\Setup\Model\ModuleStatus', [], [], '', false);

        $this->controller = new Module($this->modules, $objectManagerFactory);
    }

    public function testAllModulesValidAction()
    {
        $this->objectManager->expects($this->once())->method('create')->will($this->returnValue($this->status));
        $this->status->expects($this->exactly(2))->method('checkConstraints')->willReturn([]);
        $jsonModel = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
    }

    public function testIndexActionWithError()
    {
        $this->objectManager->expects($this->once())->method('create')->will($this->returnValue($this->status));
        $this->status->expects($this->once())
            ->method('checkConstraints')
            ->willReturn(['ModuleA', 'ModuleB']);
        $jsonModel = $this->controller->indexAction();
        $this->assertInstanceOf('\Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertArrayHasKey('error', $variables);
        $this->assertFalse($variables['success']);
    }

    /**
     * @param array $expected
     *
     * @dataProvider indexActionDataProvider
     */
    public function testIndexAction($expected)
    {
        $this->modules->expects($this->once())->method('getAllModules')->willReturn($expected['modules']);
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
        $variables = $viewModel->getVariables();
        $this->assertArrayHasKey('modules', $variables);
    }

    /**
     * @return array
     */
    public function indexActionDataProvider()
    {
        return [
            'with_modules' => [['modules' => ['module1', 'module2']]],
            'no_modules' => [['modules' => []]],
            'null_modules' => [null],
        ];
    }
}

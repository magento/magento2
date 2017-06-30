<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\Modules;

class ModulesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Module\Status
     */
    private $status;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\ModuleStatus
     */
    private $modules;

    /**
     * Controller
     *
     * @var \Magento\Setup\Controller\Modules
     */
    private $controller;

    public function setUp()
    {
        $this->objectManager = $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class);
        /** @var
         * $objectManagerProvider \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\ObjectManagerProvider
         */
        $objectManagerProvider = $this->getMock(\Magento\Setup\Model\ObjectManagerProvider::class, [], [], '', false);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($this->objectManager);
        $this->modules = $this->getMock(\Magento\Setup\Model\ModuleStatus::class, [], [], '', false);
        $this->status = $this->getMock(\Magento\Framework\Module\Status::class, [], [], '', false);
        $this->objectManager->expects($this->once())->method('create')->will($this->returnValue($this->status));
        $this->controller = new Modules($this->modules, $objectManagerProvider);
    }

    /**
     * @param array $expected
     *
     * @dataProvider indexActionDataProvider
     */
    public function testIndexAction(array $expected)
    {
        $this->modules->expects($this->once())->method('getAllModules')->willReturn($expected['modules']);
        $this->status->expects($this->once())->method('checkConstraints')->willReturn([]);
        $jsonModel = $this->controller->indexAction();
        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
    }

    /**
     * @param array $expected
     *
     * @dataProvider indexActionDataProvider
     */
    public function testIndexActionWithError(array $expected)
    {
        $this->modules->expects($this->once())->method('getAllModules')->willReturn($expected['modules']);
        $this->status->expects($this->once())
            ->method('checkConstraints')
            ->willReturn(['ModuleA', 'ModuleB']);
        $jsonModel = $this->controller->indexAction();
        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertArrayHasKey('error', $variables);
        $this->assertFalse($variables['success']);
    }

    /**
     * @return array
     */
    public function indexActionDataProvider()
    {
        return [
            'with_modules' => [['modules' => [
                'module1' => ['name' => 'module1', 'selected' => true, 'disabled' => true],
                'module2' => ['name' => 'module2', 'selected' => true, 'disabled' => true],
                'module3' => ['name' => 'module3', 'selected' => true, 'disabled' => true]
            ]]],
            'some_not_selected' => [['modules' => [
                'module1' => ['name' => 'module1', 'selected' => false, 'disabled' => true],
                'module2' => ['name' => 'module2', 'selected' => true, 'disabled' => true],
                'module3' => ['name' => 'module3', 'selected' => false, 'disabled' => true]
            ]]],
            'some_disabled' => [['modules' => [
                'module1' => ['name' => 'module1', 'selected' => true, 'disabled' => false],
                'module2' => ['name' => 'module2', 'selected' => true, 'disabled' => true],
                'module3' => ['name' => 'module3', 'selected' => true, 'disabled' => false]
            ]]],
            'no_modules' => [['modules' => []]],
        ];
    }
}

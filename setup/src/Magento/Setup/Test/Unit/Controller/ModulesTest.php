<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\View\Model\JsonModel;
use Magento\Framework\Module\Status;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Controller\Modules;
use Magento\Setup\Model\ModuleStatus;
use Magento\Setup\Model\ObjectManagerProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ModulesTest extends TestCase
{
    /**
     * @var MockObject|ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var MockObject|Status
     */
    private $status;

    /**
     * @var MockObject|ModuleStatus
     */
    private $modules;

    /**
     * Controller
     *
     * @var Modules
     */
    private $controller;

    protected function setUp(): void
    {
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        /** @var
         * $objectManagerProvider \PHPUnit\Framework\MockObject\MockObject|\Magento\Setup\Model\ObjectManagerProvider
         */
        $objectManagerProvider = $this->createMock(ObjectManagerProvider::class);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($this->objectManager);
        $this->modules = $this->createMock(ModuleStatus::class);
        $this->status = $this->createMock(Status::class);
        $this->objectManager->expects($this->once())->method('create')->willReturn($this->status);
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
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
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
        $this->assertInstanceOf(JsonModel::class, $jsonModel);
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

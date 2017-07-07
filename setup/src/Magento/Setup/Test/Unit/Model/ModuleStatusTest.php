<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use \Magento\Setup\Model\ModuleStatus;

class ModuleStatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Module\ModuleList\Loader
     */
    private $moduleLoader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Module\DependencyChecker
     */
    private $dependencyChecker;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\ObjectManagerProvider
     */
    private $objectManagerProvider;

    public function setUp()
    {
        $this->moduleLoader = $this->getMock(\Magento\Framework\Module\ModuleList\Loader::class, [], [], '', false);
        $this->dependencyChecker =
            $this->getMock(\Magento\Framework\Module\DependencyChecker::class, [], [], '', false);
        $this->deploymentConfig = $this->getMock(\Magento\Framework\App\DeploymentConfig::class, [], [], '', false);
        $this->objectManager = $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class);
        $this->objectManagerProvider =
            $this->getMock(\Magento\Setup\Model\ObjectManagerProvider::class, [], [], '', false);
        $this->objectManagerProvider->expects($this->once())->method('get')->willReturn($this->objectManager);
        $this->objectManager->expects($this->once())->method('get')->willReturn($this->dependencyChecker);
    }

    /**
     * @param array $expectedAllModules
     * @param array $expectedConfig
     * @param array $expectedResult
     *
     * @dataProvider getAllModulesDataProvider
     */
    public function testGetAllModules($expectedAllModules, $expectedConfig, $expectedResult)
    {
        $this->moduleLoader->expects($this->once())->method('load')->will($this->returnValue($expectedAllModules));
        $this->deploymentConfig->expects($this->once())->method('get')
            ->will($this->returnValue($expectedConfig));
        $this->dependencyChecker->expects($this->any())->method('checkDependenciesWhenDisableModules')->willReturn(
            ['module1' => [], 'module2' => [], 'module3' => [], 'module4' => []]
        );

        $moduleStatus = new ModuleStatus($this->moduleLoader, $this->deploymentConfig, $this->objectManagerProvider);
        $allModules = $moduleStatus->getAllModules();
        $this->assertSame($expectedResult[0], $allModules['module1']['selected']);
        $this->assertSame($expectedResult[1], $allModules['module2']['selected']);
        $this->assertSame($expectedResult[2], $allModules['module3']['selected']);
        $this->assertSame($expectedResult[3], $allModules['module4']['selected']);
    }

    /**
     * @param array $expectedAllModules
     * @param array $expectedConfig
     * @param array $expectedResult
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @dataProvider getAllModulesDataProvider
     */
    public function testGetAllModulesWithInputs($expectedAllModules, $expectedConfig, $expectedResult)
    {
        $this->moduleLoader->expects($this->once())->method('load')->will($this->returnValue($expectedAllModules));
        $this->deploymentConfig->expects($this->never())->method('get')
            ->will($this->returnValue($expectedConfig));
        $this->dependencyChecker->expects($this->any())->method('checkDependenciesWhenDisableModules')->willReturn(
            ['module1' => [], 'module2' => [], 'module3' => [], 'module4' => []]
        );

        $moduleStatus = new ModuleStatus($this->moduleLoader, $this->deploymentConfig, $this->objectManagerProvider);
        $allModules = $moduleStatus->getAllModules(['module1', 'module2']);
        $this->assertSame(true, $allModules['module1']['selected']);
        $this->assertSame(true, $allModules['module2']['selected']);
        $this->assertSame(false, $allModules['module3']['selected']);
        $this->assertSame(false, $allModules['module4']['selected']);
    }

    /**
     * @param array $expectedAllModules
     * @param array $expectedConfig
     * @param array $expectedResult
     *
     * @dataProvider getAllModulesDataProvider
     */
    public function testSetIsEnabled($expectedAllModules, $expectedConfig, $expectedResult)
    {
        $this->moduleLoader->expects($this->once())->method('load')->will($this->returnValue($expectedAllModules));
        $this->deploymentConfig->expects($this->once())->method('get')
            ->will($this->returnValue($expectedConfig));
        $this->dependencyChecker->expects($this->any())->method('checkDependenciesWhenDisableModules')->willReturn(
            ['module1' => [], 'module2' => [], 'module3' => [], 'module4' => []]
        );

        $moduleStatus = new ModuleStatus($this->moduleLoader, $this->deploymentConfig, $this->objectManagerProvider);
        $moduleStatus->setIsEnabled(false, 'module1');
        $allModules = $moduleStatus->getAllModules();
        $this->assertSame(false, $allModules['module1']['selected']);
        $this->assertSame($expectedResult[1], $allModules['module2']['selected']);
        $this->assertSame($expectedResult[2], $allModules['module3']['selected']);
        $this->assertSame($expectedResult[3], $allModules['module4']['selected']);
    }

    /**
     * @return array
     */
    public function getAllModulesDataProvider()
    {
        $expectedAllModules = [
            'module1' => ['name' => 'module1'],
            'module2' => ['name' => 'module2'],
            'module3' => ['name' => 'module3'],
            'module4' => ['name' => 'module4']
        ];
        $expectedConfig = ['module1' => 0, 'module3' => 0];
        return [
            [$expectedAllModules, $expectedConfig, [false, true, false, true]],
            [$expectedAllModules, [], [true, true, true, true]],
        ];
    }
}

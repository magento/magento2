<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Module\DependencyChecker;
use Magento\Framework\Module\ModuleList\Loader;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Model\ModuleStatus;
use Magento\Setup\Model\ObjectManagerProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ModuleStatusTest extends TestCase
{
    /**
     * @var MockObject|Loader
     */
    private $moduleLoader;

    /**
     * @var MockObject|DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var MockObject|DependencyChecker
     */
    private $dependencyChecker;

    /**
     * @var MockObject|ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var MockObject|ObjectManagerProvider
     */
    private $objectManagerProvider;

    protected function setUp(): void
    {
        $this->moduleLoader = $this->createMock(Loader::class);
        $this->dependencyChecker =
            $this->createMock(DependencyChecker::class);
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->objectManagerProvider =
            $this->createMock(ObjectManagerProvider::class);
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
        $this->moduleLoader->expects($this->once())->method('load')->willReturn($expectedAllModules);
        $this->deploymentConfig->expects($this->once())->method('get')
            ->willReturn($expectedConfig);
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
        $this->moduleLoader->expects($this->once())->method('load')->willReturn($expectedAllModules);
        $this->deploymentConfig->expects($this->never())->method('get')
            ->willReturn($expectedConfig);
        $this->dependencyChecker->expects($this->any())->method('checkDependenciesWhenDisableModules')->willReturn(
            ['module1' => [], 'module2' => [], 'module3' => [], 'module4' => []]
        );

        $moduleStatus = new ModuleStatus($this->moduleLoader, $this->deploymentConfig, $this->objectManagerProvider);
        $allModules = $moduleStatus->getAllModules(['module1', 'module2']);
        $this->assertTrue($allModules['module1']['selected']);
        $this->assertTrue($allModules['module2']['selected']);
        $this->assertFalse($allModules['module3']['selected']);
        $this->assertFalse($allModules['module4']['selected']);
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
        $this->moduleLoader->expects($this->once())->method('load')->willReturn($expectedAllModules);
        $this->deploymentConfig->expects($this->once())->method('get')
            ->willReturn($expectedConfig);
        $this->dependencyChecker->expects($this->any())->method('checkDependenciesWhenDisableModules')->willReturn(
            ['module1' => [], 'module2' => [], 'module3' => [], 'module4' => []]
        );

        $moduleStatus = new ModuleStatus($this->moduleLoader, $this->deploymentConfig, $this->objectManagerProvider);
        $moduleStatus->setIsEnabled(false, 'module1');
        $allModules = $moduleStatus->getAllModules();
        $this->assertFalse($allModules['module1']['selected']);
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

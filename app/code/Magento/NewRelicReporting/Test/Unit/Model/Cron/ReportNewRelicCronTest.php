<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model\Cron;

use Magento\NewRelicReporting\Model\Apm\Deployments;
use Magento\NewRelicReporting\Model\Apm\DeploymentsFactory;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\Counter;
use Magento\NewRelicReporting\Model\Cron\ReportNewRelicCron;
use Magento\NewRelicReporting\Model\CronEvent;
use Magento\NewRelicReporting\Model\CronEventFactory;
use Magento\NewRelicReporting\Model\Module\Collect;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class ReportNewRelicCronTest extends TestCase
{
    /**
     * @var ReportNewRelicCron
     */
    protected $model;

    /**
     * @var MockObject&Config
     */
    protected $config;

    /**
     * @var MockObject&Collect
     */
    protected $collect;

    /**
     * @var MockObject&Counter
     */
    protected $counter;

    /**
     * @var MockObject&CronEventFactory
     */
    protected $cronEventFactory;

    /**
     * @var MockObject&CronEvent
     */
    protected $cronEventModel;

    /**
     * @var MockObject&DeploymentsFactory
     */
    protected $deploymentsFactory;

    /**
     * @var MockObject&Deployments
     */
    protected $deploymentsModel;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isNewRelicEnabled'])
            ->getMock();
        $this->collect = $this->getMockBuilder(Collect::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getModuleData'])
            ->getMock();
        $this->counter = $this->getMockBuilder(Counter::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getAllProductsCount',
                'getConfigurableCount',
                'getActiveCatalogSize',
                'getCategoryCount',
                'getWebsiteCount',
                'getStoreViewsCount',
                'getCustomerCount',
            ])
            ->getMock();
        $this->cronEventFactory = $this->getMockBuilder(CronEventFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->cronEventModel = $this->getMockBuilder(CronEvent::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addData', 'sendRequest'])
            ->getMock();
        $this->deploymentsFactory = $this->getMockBuilder(
            DeploymentsFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->deploymentsModel = $this->getMockBuilder(Deployments::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setDeployment'])
            ->getMock();

        $this->cronEventFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->cronEventModel);
        $this->deploymentsFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->deploymentsModel);

        $this->model = new ReportNewRelicCron(
            $this->config,
            $this->collect,
            $this->counter,
            $this->cronEventFactory,
            $this->deploymentsFactory
        );
    }

    /**
     * Test case when module is disabled in config
     *
     * @return void
     */
    public function testReportNewRelicCronModuleDisabledFromConfig()
    {
        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(false);

        $this->assertSame(
            $this->model,
            $this->model->report()
        );
    }

    /**
     * Test case when module is enabled
     *
     * @return void
     */
    public function testReportNewRelicCron()
    {
        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $this->counter->expects($this->once())
            ->method('getAllProductsCount');
        $this->counter->expects($this->once())
            ->method('getConfigurableCount');
        $this->counter->expects($this->once())
            ->method('getActiveCatalogSize');
        $this->counter->expects($this->once())
            ->method('getCategoryCount');
        $this->counter->expects($this->once())
            ->method('getWebsiteCount');
        $this->counter->expects($this->once())
            ->method('getStoreViewsCount');
        $this->counter->expects($this->once())
            ->method('getCustomerCount');
        $this->cronEventModel->expects($this->once())
            ->method('addData')
            ->willReturnSelf();
        $this->cronEventModel->expects($this->once())
            ->method('sendRequest');

        $this->deploymentsModel->expects($this->any())
            ->method('setDeployment');

        $this->assertSame(
            $this->model,
            $this->model->report()
        );
    }

    /**
     * Test case when module is enabled and request is failed
     */
    public function testReportNewRelicCronRequestFailed()
    {
        $this->expectException('Exception');
        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $this->counter->expects($this->once())
            ->method('getAllProductsCount');
        $this->counter->expects($this->once())
            ->method('getConfigurableCount');
        $this->counter->expects($this->once())
            ->method('getActiveCatalogSize');
        $this->counter->expects($this->once())
            ->method('getCategoryCount');
        $this->counter->expects($this->once())
            ->method('getWebsiteCount');
        $this->counter->expects($this->once())
            ->method('getStoreViewsCount');
        $this->counter->expects($this->once())
            ->method('getCustomerCount');
        $this->cronEventModel->expects($this->once())
            ->method('addData')
            ->willReturnSelf();
        $this->cronEventModel->expects($this->once())
            ->method('sendRequest');

        $this->cronEventModel->expects($this->once())->method('sendRequest')->willThrowException(new \Exception());

        $this->deploymentsModel->expects($this->any())
            ->method('setDeployment');

        $this->model->report();
    }

    /**
     * Test reportModules method with module changes
     *
     * @return void
     * @throws ReflectionException
     */
    public function testReportModulesWithChanges()
    {
        $moduleData = [
            'changes' => [
                [
                    'type' => Config::ENABLED,
                    'name' => 'Test_Module1',
                    'setup_version' => '1.0.0'
                ],
                [
                    'type' => Config::DISABLED,
                    'name' => 'Test_Module2',
                    'setup_version' => '1.1.0'
                ],
                [
                    'type' => Config::INSTALLED,
                    'name' => 'Test_Module3',
                    'setup_version' => '2.0.0'
                ],
                [
                    'type' => Config::UNINSTALLED,
                    'name' => 'Test_Module4',
                    'setup_version' => '1.5.0'
                ]
            ],
            Config::ENABLED => 10,
            Config::DISABLED => 5,
            Config::INSTALLED => 15
        ];

        $this->collect->expects($this->once())
            ->method('getModuleData')
            ->with(false)
            ->willReturn($moduleData);

        // Expect deployment calls for each change type
        $this->deploymentsModel->expects($this->exactly(4))
            ->method('setDeployment');

        // Use reflection to call protected method
        $reflection = new ReflectionClass($this->model);
        $method = $reflection->getMethod('reportModules');
        $method->invoke($this->model);

        // Verify custom parameters were added
        $property = $reflection->getProperty('customParameters');
        $customParams = $property->getValue($this->model);

        $this->assertEquals(10, $customParams[Config::MODULES_ENABLED]);
        $this->assertEquals(5, $customParams[Config::MODULES_DISABLED]);
        $this->assertEquals(15, $customParams[Config::MODULES_INSTALLED]);
    }

    /**
     * Test reportModules method with no changes
     *
     * @return void
     * @throws ReflectionException
     */
    public function testReportModulesWithNoChanges()
    {
        $moduleData = [
            'changes' => [],
            Config::ENABLED => 8,
            Config::DISABLED => 3,
            Config::INSTALLED => 11
        ];

        $this->collect->expects($this->once())
            ->method('getModuleData')
            ->with(false)
            ->willReturn($moduleData);

        // Should not call setDeployment when no changes
        $this->deploymentsModel->expects($this->never())
            ->method('setDeployment');

        // Use reflection to call protected method
        $reflection = new ReflectionClass($this->model);
        $method = $reflection->getMethod('reportModules');
        $method->invoke($this->model);

        // Verify custom parameters were still added
        $property = $reflection->getProperty('customParameters');
        $customParams = $property->getValue($this->model);

        $this->assertEquals(8, $customParams[Config::MODULES_ENABLED]);
        $this->assertEquals(3, $customParams[Config::MODULES_DISABLED]);
        $this->assertEquals(11, $customParams[Config::MODULES_INSTALLED]);
    }

    /**
     * Test setModuleChangeStatusDeployment with changes
     *
     * @return void
     * @throws ReflectionException
     */
    public function testSetModuleChangeStatusDeploymentWithChanges()
    {
        $changesArray = ['Module1-1.0.0', 'Module2-2.0.0'];
        $deploymentText = 'Test Deployment';

        $this->deploymentsModel->expects($this->exactly(2))
            ->method('setDeployment');

        // Use reflection to call protected method
        $reflection = new ReflectionClass($this->model);
        $method = $reflection->getMethod('setModuleChangeStatusDeployment');
        $method->invoke($this->model, $changesArray, $deploymentText);
    }

    /**
     * Test setModuleChangeStatusDeployment with empty changes
     *
     * @return void
     * @throws ReflectionException
     */
    public function testSetModuleChangeStatusDeploymentWithEmptyChanges()
    {
        $changesArray = [];
        $deploymentText = 'Test Deployment';

        // Should not call setDeployment when array is empty
        $this->deploymentsModel->expects($this->never())
            ->method('setDeployment');

        // Use reflection to call protected method
        $reflection = new ReflectionClass($this->model);
        $method = $reflection->getMethod('setModuleChangeStatusDeployment');
        $method->invoke($this->model, $changesArray, $deploymentText);
    }

    /**
     * Data provider for module change types
     *
     * @return array
     */
    public static function moduleChangeTypesDataProvider(): array
    {
        return [
            'enabled_modules' => [Config::ENABLED, 'Test_Module_Enabled', '1.0.0'],
            'disabled_modules' => [Config::DISABLED, 'Test_Module_Disabled', '1.1.0'],
            'installed_modules' => [Config::INSTALLED, 'Test_Module_Installed', '2.0.0'],
            'uninstalled_modules' => [Config::UNINSTALLED, 'Test_Module_Uninstalled', '1.5.0']
        ];
    }

    /**
     * Test reportModules handles different module change types correctly
     *
     * @param string $changeType
     * @param string $moduleName
     * @param string $version
     * @return void     * @throws ReflectionException
     */
    #[DataProvider('moduleChangeTypesDataProvider')]
    public function testReportModulesHandlesChangeTypes(string $changeType, string $moduleName, string $version)
    {
        $moduleData = [
            'changes' => [
                [
                    'type' => $changeType,
                    'name' => $moduleName,
                    'setup_version' => $version
                ]
            ],
            Config::ENABLED => 1,
            Config::DISABLED => 1,
            Config::INSTALLED => 1
        ];

        $this->collect->expects($this->once())
            ->method('getModuleData')
            ->with(false)
            ->willReturn($moduleData);

        $this->deploymentsModel->expects($this->once())
            ->method('setDeployment');

        // Use reflection to call protected method
        $reflection = new ReflectionClass($this->model);
        $method = $reflection->getMethod('reportModules');
        $method->invoke($this->model);
    }

    /**
     * Test reportModules switch statement coverage for all cases
     *
     * @return void
     * @throws ReflectionException
     */
    public function testReportModulesAllSwitchCases()
    {
        $moduleData = [
            'changes' => [
                ['type' => Config::ENABLED, 'name' => 'EnabledModule', 'setup_version' => '1.0.0'],
                ['type' => Config::DISABLED, 'name' => 'DisabledModule', 'setup_version' => '1.0.0'],
                ['type' => Config::INSTALLED, 'name' => 'InstalledModule', 'setup_version' => '1.0.0'],
                ['type' => Config::UNINSTALLED, 'name' => 'UninstalledModule', 'setup_version' => '1.0.0'],
                ['type' => 'unknown_type', 'name' => 'UnknownModule', 'setup_version' => '1.0.0'] // Test default case
            ],
            Config::ENABLED => 1,
            Config::DISABLED => 1,
            Config::INSTALLED => 1
        ];

        $this->collect->expects($this->once())
            ->method('getModuleData')
            ->with(false)
            ->willReturn($moduleData);

        // Expect 4 deployment calls (unknown_type should not trigger deployment)
        $this->deploymentsModel->expects($this->exactly(4))
            ->method('setDeployment');

        // Use reflection to call protected method
        $reflection = new ReflectionClass($this->model);
        $method = $reflection->getMethod('reportModules');
        $method->invoke($this->model);
    }
}

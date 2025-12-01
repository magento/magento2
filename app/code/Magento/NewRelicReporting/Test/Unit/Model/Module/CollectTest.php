<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model\Module;

use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\Manager;
use Magento\Framework\Module\ModuleListInterface;
use Magento\NewRelicReporting\Model\Module;
use Magento\NewRelicReporting\Model\Module\Collect;
use Magento\NewRelicReporting\Model\ModuleFactory;
use Magento\NewRelicReporting\Model\ResourceModel\Module\Collection;
use Magento\NewRelicReporting\Model\ResourceModel\Module\CollectionFactory;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class CollectTest extends TestCase
{
    /**
     * @var Collect
     */
    protected $model;

    /**
     * @var ModuleListInterface|MockObject
     */
    protected $moduleListMock;

    /**
     * @var Manager|MockObject
     */
    protected $moduleManagerMock;

    /**
     * @var fullModuleList|MockObject
     */
    protected $fullModuleListMock;

    /**
     * @var ModuleFactory|MockObject
     */
    protected $moduleFactoryMock;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $moduleCollectionFactoryMock;

    protected function setUp(): void
    {
        $this->moduleListMock = $this->getMockBuilder(ModuleListInterface::class)
            ->onlyMethods(['getNames', 'has', 'getAll', 'getOne'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->fullModuleListMock = $this->getMockBuilder(FullModuleList::class)
            ->onlyMethods(['getNames', 'getAll'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->moduleManagerMock = $this->getMockBuilder(Manager::class)
            ->onlyMethods(['isOutputEnabled'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->moduleFactoryMock = $this->createPartialMock(
            ModuleFactory::class,
            ['create']
        );

        $this->moduleCollectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->model = new Collect(
            $this->moduleListMock,
            $this->fullModuleListMock,
            $this->moduleManagerMock,
            $this->moduleFactoryMock,
            $this->moduleCollectionFactoryMock
        );
    }

    /**
     * Tests modules data returns array
     *
     * @return void
     * @throws Exception
     */
    public function testGetModuleDataWithoutRefresh()
    {
        $moduleCollectionMock = $this->getMockBuilder(
            Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock = $this->createMock(Module::class);
        $modulesMockArray = [
            'Module_Name' => [
                'name' => 'Name',
                'setup_version' => '2.0.0',
                'sequence' => []
            ]
        ];
        $testChangesMockArray = [
            ['entity' => '3',
                'name' => 'Name',
                'active' => 'true',
                'state' => 'enabled',
                'setup_version' => '2.0.0',
                'updated_at' => '2015-09-02 18:38:17'],
            ['entity' => '4',
                'name' => 'Name',
                'active' => 'true',
                'state' => 'disabled',
                'setup_version' => '2.0.0',
                'updated_at' => '2015-09-02 18:38:17'],
            ['entity' => '5',
                'name' => 'Name',
                'active' => 'true',
                'state' => 'uninstalled',
                'setup_version' => '2.0.0',
                'updated_at' => '2015-09-02 18:38:17']
        ];
        $itemMockArray = [$itemMock];
        $enabledModulesMockArray = [];

        $this->moduleCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($moduleCollectionMock);

        $this->moduleFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($itemMock);

        $itemMock->expects($this->any())
            ->method('setData')
            ->willReturnSelf();

        $itemMock->expects($this->any())
            ->method('save')
            ->willReturnSelf();

        $moduleCollectionMock->expects($this->any())
            ->method('getItems')
            ->willReturn($itemMockArray);

        $moduleCollectionMock->expects($this->any())
            ->method('getData')
            ->willReturn($testChangesMockArray);

        $this->fullModuleListMock->expects($this->once())
            ->method('getAll')
            ->willReturn($modulesMockArray);

        $this->fullModuleListMock->expects($this->once())
            ->method('getNames')
            ->willReturn($enabledModulesMockArray);

        $this->moduleListMock->expects($this->once())
            ->method('getNames')
            ->willReturn($enabledModulesMockArray);

        $this->assertIsArray($this->model->getModuleData());
    }

    /**
     * Tests modules data returns array and saving in DB
     *
     * @dataProvider itemDataProvider
     * @return void
     */
    public function testGetModuleDataRefresh($data)
    {
        $moduleCollectionMock = $this->getMockBuilder(
            Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Module|MockObject $itemMock */
        $itemMock = $this->getMockBuilder(Module::class)
            ->addMethods(['getName', 'getState'])
            ->onlyMethods(['getData', 'setData', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $modulesMockArray = [
            'Module_Name1' => [
                'name' => 'Module_Name1',
                'setup_version' => '2.0.0',
                'sequence' => []
            ]
        ];
        $itemMock->setData($data);
        $testChangesMockArray = [
            'entity_id' => '3',
            'name' => 'Name',
            'active' => 'true',
            'state' => 'uninstalled',
            'setup_version' => '2.0.0',
            'some_param' => 'some_value',
            'updated_at' => '2015-09-02 18:38:17'
        ];
        $itemMockArray = [$itemMock];

        $enabledModulesMockArray = ['Module_Name2'];
        $allModulesMockArray = ['Module_Name1','Module_Name2'];

        $this->moduleCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($moduleCollectionMock);

        $this->moduleFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($itemMock);

        $itemMock->expects($this->any())
            ->method('setData')
            ->willReturnSelf();

        $itemMock->expects($this->any())
            ->method('save')
            ->willReturnSelf();

        $itemMock->expects($this->any())
            ->method('getState')
            ->willReturn($data['state']);

        $itemMock->expects($this->any())
            ->method('getName')
            ->willReturn($data['name']);

        $moduleCollectionMock->expects($this->any())
            ->method('getItems')
            ->willReturn($itemMockArray);

        $itemMock->expects($this->any())
            ->method('getData')
            ->willReturn($testChangesMockArray);

        $this->fullModuleListMock->expects($this->once())
            ->method('getAll')
            ->willReturn($modulesMockArray);

        $this->fullModuleListMock->expects($this->any())
            ->method('getNames')
            ->willReturn($allModulesMockArray);

        $this->moduleListMock->expects($this->any())
            ->method('getNames')
            ->willReturn($enabledModulesMockArray);

        $this->assertIsArray($this->model->getModuleData());
    }

    /**
     * Tests modules data returns array and saving in DB
     *
     * @dataProvider itemDataProvider
     * @return void
     */
    public function testGetModuleDataRefreshOrStatement($data)
    {
        $moduleCollectionMock = $this->getMockBuilder(
            Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Module|MockObject $itemMock */
        $itemMock = $this->getMockBuilder(Module::class)
            ->addMethods(['getName', 'getState'])
            ->onlyMethods(['getData', 'setData', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $modulesMockArray = [
            'Module_Name1' => [
                'name' => 'Module_Name1',
                'setup_version' => '2.0.0',
                'sequence' => []
            ]
        ];
        $itemMock->setData($data);
        $testChangesMockArray = [
            'entity_id' => '3',
            'name' => 'Name',
            'active' => 'false',
            'state' => 'enabled',
            'setup_version' => '2.0.0',
            'some_param' => 'some_value',
            'updated_at' => '2015-09-02 18:38:17'
        ];
        $itemMockArray = [$itemMock];

        $enabledModulesMockArray = ['Module_Name2'];
        $allModulesMockArray = ['Module_Name1','Module_Name2'];

        $this->moduleCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($moduleCollectionMock);

        $this->moduleFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($itemMock);

        $itemMock->expects($this->any())
            ->method('setData')
            ->willReturnSelf();

        $itemMock->expects($this->any())
            ->method('save')
            ->willReturnSelf();

        $itemMock->expects($this->any())
            ->method('getState')
            ->willReturn($data['state']);

        $itemMock->expects($this->any())
            ->method('getName')
            ->willReturn($data['name']);

        $moduleCollectionMock->expects($this->any())
            ->method('getItems')
            ->willReturn($itemMockArray);

        $itemMock->expects($this->any())
            ->method('getData')
            ->willReturn($testChangesMockArray);

        $this->fullModuleListMock->expects($this->once())
            ->method('getAll')
            ->willReturn($modulesMockArray);

        $this->fullModuleListMock->expects($this->any())
            ->method('getNames')
            ->willReturn($allModulesMockArray);

        $this->moduleListMock->expects($this->any())
            ->method('getNames')
            ->willReturn($enabledModulesMockArray);

        $this->assertIsArray($this->model->getModuleData());
    }

    /**
     * @return array
     */
    public static function itemDataProvider()
    {
        return [
            [
                [
                    'entity_id' => '1',
                    'name' => 'Module_Name1',
                    'active' => 'true',
                    'state' => 'enabled',
                    'setup_version' => '2.0.0'
                ]
            ],
            [
                [
                    'entity_id' => '2',
                    'name' => 'Module_Name2',
                    'active' => 'true',
                    'state' => 'disabled',
                    'setup_version' => '2.0.0'
                ]
            ],
            [
                [
                    'entity_id' => '2',
                    'name' => 'Module_Name2',
                    'active' => 'true',
                    'state' => 'uninstalled',
                    'setup_version' => '2.0.0'
                ]
            ]
        ];
    }

    /**
     * Test getState method when module output is enabled
     *
     * @return void
     * @throws ReflectionException
     */
    public function testGetStateWhenModuleOutputEnabled()
    {
        $moduleName = 'Test_Module';

        // Mock isOutputEnabled to return true
        $this->moduleManagerMock->expects($this->once())
            ->method('isOutputEnabled')
            ->with($moduleName)
            ->willReturn(true);

        // Use reflection to call the protected getState method
        $reflection = new ReflectionClass($this->model);
        $method = $reflection->getMethod('getState');
        $method->setAccessible(true);

        $result = $method->invoke($this->model, $moduleName);

        // Should return 'enabled' when isOutputEnabled returns true
        $this->assertEquals('enabled', $result);
    }

    /**
     * Test getState method when module output is disabled
     *
     * @return void
     * @throws ReflectionException
     */
    public function testGetStateWhenModuleOutputDisabled()
    {
        $moduleName = 'Test_Module';

        // Mock isOutputEnabled to return false
        $this->moduleManagerMock->expects($this->once())
            ->method('isOutputEnabled')
            ->with($moduleName)
            ->willReturn(false);

        // Use reflection to call the protected getState method
        $reflection = new ReflectionClass($this->model);
        $method = $reflection->getMethod('getState');
        $method->setAccessible(true);

        $result = $method->invoke($this->model, $moduleName);

        // Should return 'disabled' when isOutputEnabled returns false
        $this->assertEquals('disabled', $result);
    }

    /**
     * Data provider for getState test scenarios
     *
     * @return array
     */
    public static function getStateDataProvider(): array
    {
        return [
            'module_enabled' => ['TestModule_Enabled', true, 'enabled'],
            'module_disabled' => ['TestModule_Disabled', false, 'disabled']
        ];
    }

    /**
     * Test getState method with data provider
     *
     * @param string $moduleName
     * @param bool $isOutputEnabled
     * @param string $expectedState
     * @return void
     * @dataProvider getStateDataProvider
     * @throws ReflectionException
     */
    public function testGetStateWithDataProvider(string $moduleName, bool $isOutputEnabled, string $expectedState)
    {
        // Mock isOutputEnabled with the provided return value
        $this->moduleManagerMock->expects($this->once())
            ->method('isOutputEnabled')
            ->with($moduleName)
            ->willReturn($isOutputEnabled);

        // Use reflection to call the protected getState method
        $reflection = new ReflectionClass($this->model);
        $method = $reflection->getMethod('getState');
        $method->setAccessible(true);

        $result = $method->invoke($this->model, $moduleName);

        $this->assertEquals($expectedState, $result);
    }
}

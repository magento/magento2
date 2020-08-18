<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
            ->setMethods(['getNames', 'has', 'getAll', 'getOne'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->fullModuleListMock = $this->getMockBuilder(FullModuleList::class)
            ->setMethods(['getNames', 'getAll'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->moduleManagerMock = $this->getMockBuilder(Manager::class)
            ->setMethods(['isOutputEnabled'])
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
    public function itemDataProvider()
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
}

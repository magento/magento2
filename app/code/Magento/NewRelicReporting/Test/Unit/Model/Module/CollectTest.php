<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Test\Unit\Model\Module;

use Magento\NewRelicReporting\Model\Module\Collect;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Module\Manager;
use Magento\NewRelicReporting\Model\Module;

/**
 * Class CollectTest
 *
 * @codingStandardsIgnoreFile
 */
class CollectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\NewRelicReporting\Model\Module\Collect
     */
    protected $model;

    /**
     * @var ModuleListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleListMock;

    /**
     * @var Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleManagerMock;

    /**
     * @var fullModuleList|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fullModuleListMock;

    /**
     * @var \Magento\NewRelicReporting\Model\ModuleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleFactoryMock;

    /**
     * @var \Magento\NewRelicReporting\Model\ResourceModel\Module\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleCollectionFactoryMock;

    protected function setUp()
    {
        $this->moduleListMock = $this->getMockBuilder(\Magento\Framework\Module\ModuleListInterface::class)
            ->setMethods(['getNames', 'has', 'getAll', 'getOne'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->fullModuleListMock = $this->getMockBuilder(\Magento\Framework\Module\FullModuleList::class)
            ->setMethods(['getNames', 'getAll'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->moduleManagerMock = $this->getMockBuilder(\Magento\Framework\Module\Manager::class)
            ->setMethods(['isOutputEnabled'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->moduleFactoryMock = $this->getMock(
            \Magento\NewRelicReporting\Model\ModuleFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->moduleCollectionFactoryMock = $this->getMock(
            \Magento\NewRelicReporting\Model\ResourceModel\Module\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
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
            \Magento\NewRelicReporting\Model\ResourceModel\Module\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock = $this->getMock(\Magento\NewRelicReporting\Model\Module::class, [], [], '', false);
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

        $this->moduleManagerMock->expects($this->any())->method('isOutputEnabled')->will(
            $this->returnValue(false)
        );

        $this->assertInternalType(
            'array',
            $this->model->getModuleData()
        );
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
            \Magento\NewRelicReporting\Model\ResourceModel\Module\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\NewRelicReporting\Model\Module|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->getMock(
            \Magento\NewRelicReporting\Model\Module::class,
            ['getName', 'getData', 'setData', 'getState', 'save'],
            [],
            '',
            false
        );
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

        $this->moduleManagerMock->expects($this->any())->method('isOutputEnabled')->will(
            $this->returnValue(true)
        );

        $this->assertInternalType(
            'array',
            $this->model->getModuleData()
        );
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
            \Magento\NewRelicReporting\Model\ResourceModel\Module\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\NewRelicReporting\Model\Module|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->getMock(
            \Magento\NewRelicReporting\Model\Module::class,
            ['getName', 'getData', 'setData', 'getState', 'save'],
            [],
            '',
            false
        );
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

        $this->moduleManagerMock->expects($this->any())->method('isOutputEnabled')->will(
            $this->returnValue(true)
        );

        $this->assertInternalType(
            'array',
            $this->model->getModuleData()
        );
    }

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

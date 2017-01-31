<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Test\Unit\Model\Rule;

class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Rule\DataProvider
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataPersistorMock;

    protected function setUp()
    {
        $this->collectionFactoryMock = $this->getMock(
            'Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->collectionMock = $this->getMock(
            'Magento\CatalogRule\Model\ResourceModel\Rule\Collection',
            [],
            [],
            '',
            false
        );
        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->collectionMock);
        $this->dataPersistorMock = $this->getMock('Magento\Framework\App\Request\DataPersistorInterface');

        $this->model = new \Magento\CatalogRule\Model\Rule\DataProvider(
            'Name',
            'Primary',
            'Request',
            $this->collectionFactoryMock,
            $this->dataPersistorMock
        );
    }

    public function testGetData()
    {
        $ruleId = 42;
        $ruleData = ['name' => 'Catalog Price Rule'];

        $ruleMock = $this->getMock('Magento\CatalogRule\Model\Rule', [], [], '', false);
        $this->collectionMock->expects($this->once())->method('getItems')->willReturn([$ruleMock]);

        $ruleMock->expects($this->atLeastOnce())->method('getId')->willReturn($ruleId);
        $ruleMock->expects($this->once())->method('load')->willReturnSelf();
        $ruleMock->expects($this->once())->method('getData')->willReturn($ruleData);
        $this->dataPersistorMock->expects($this->once())->method('get')->with('catalog_rule')->willReturn(null);

        $this->assertEquals([$ruleId => $ruleData], $this->model->getData());
        // Load from object-cache the second time
        $this->assertEquals([$ruleId => $ruleData], $this->model->getData());
    }

    public function testGetDataIfRulePersisted()
    {
        $ruleId = 42;
        $ruleData = ['name' => 'Catalog Price Rule'];
        $this->collectionMock->expects($this->once())->method('getItems')->willReturn([]);

        $persistedData = ['key' => 'value'];
        $this->dataPersistorMock->expects($this->once())
            ->method('get')
            ->with('catalog_rule')
            ->willReturn($persistedData);
        $this->dataPersistorMock->expects($this->once())
            ->method('clear')
            ->with('catalog_rule');

        $newRuleMock = $this->getMock('Magento\CatalogRule\Model\Rule', [], [], '', false);
        $newRuleMock->expects($this->atLeastOnce())->method('setData')->with($persistedData)->willReturnSelf();
        $newRuleMock->expects($this->atLeastOnce())->method('getId')->willReturn($ruleId);
        $newRuleMock->expects($this->once())->method('getData')->willReturn($ruleData);
        $this->collectionMock->expects($this->once())->method('getNewEmptyItem')->willReturn($newRuleMock);

        $this->assertEquals([$ruleId => $ruleData], $this->model->getData());
        // Load from object-cache the second time
        $this->assertEquals([$ruleId => $ruleData], $this->model->getData());
    }
}

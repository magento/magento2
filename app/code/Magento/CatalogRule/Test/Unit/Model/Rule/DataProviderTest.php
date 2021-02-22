<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Test\Unit\Model\Rule;

class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Rule\DataProvider
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $collectionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $dataPersistorMock;

    protected function setUp(): void
    {
        $this->collectionFactoryMock = $this->createPartialMock(
            \Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory::class,
            ['create']
        );
        $this->collectionMock = $this->createMock(\Magento\CatalogRule\Model\ResourceModel\Rule\Collection::class);
        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->collectionMock);
        $this->dataPersistorMock = $this->createMock(\Magento\Framework\App\Request\DataPersistorInterface::class);

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

        $ruleMock = $this->createMock(\Magento\CatalogRule\Model\Rule::class);
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

        $newRuleMock = $this->createMock(\Magento\CatalogRule\Model\Rule::class);
        $newRuleMock->expects($this->atLeastOnce())->method('setData')->with($persistedData)->willReturnSelf();
        $newRuleMock->expects($this->atLeastOnce())->method('getId')->willReturn($ruleId);
        $newRuleMock->expects($this->once())->method('getData')->willReturn($ruleData);
        $this->collectionMock->expects($this->once())->method('getNewEmptyItem')->willReturn($newRuleMock);

        $this->assertEquals([$ruleId => $ruleData], $this->model->getData());
        // Load from object-cache the second time
        $this->assertEquals([$ruleId => $ruleData], $this->model->getData());
    }
}

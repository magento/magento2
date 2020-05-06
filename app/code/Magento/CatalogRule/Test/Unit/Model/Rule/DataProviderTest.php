<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Rule;

use Magento\CatalogRule\Model\ResourceModel\Rule\Collection;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\Rule\DataProvider;
use Magento\Framework\App\Request\DataPersistorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataProviderTest extends TestCase
{
    /**
     * @var DataProvider
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var MockObject
     */
    protected $collectionMock;

    /**
     * @var MockObject
     */
    protected $dataPersistorMock;

    protected function setUp(): void
    {
        $this->collectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->collectionMock = $this->createMock(Collection::class);
        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->collectionMock);
        $this->dataPersistorMock = $this->getMockForAbstractClass(DataPersistorInterface::class);

        $this->model = new DataProvider(
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

        $ruleMock = $this->createMock(Rule::class);
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

        $newRuleMock = $this->createMock(Rule::class);
        $newRuleMock->expects($this->atLeastOnce())->method('setData')->with($persistedData)->willReturnSelf();
        $newRuleMock->expects($this->atLeastOnce())->method('getId')->willReturn($ruleId);
        $newRuleMock->expects($this->once())->method('getData')->willReturn($ruleData);
        $this->collectionMock->expects($this->once())->method('getNewEmptyItem')->willReturn($newRuleMock);

        $this->assertEquals([$ruleId => $ruleData], $this->model->getData());
        // Load from object-cache the second time
        $this->assertEquals([$ruleId => $ruleData], $this->model->getData());
    }
}

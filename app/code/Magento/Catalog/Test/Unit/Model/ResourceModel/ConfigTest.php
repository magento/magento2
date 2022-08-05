<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\Config;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Catalog\Model\ResourceModel\Config
 */
class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    private $model;

    /**
     * @var MockObject
     */
    private $resource;

    /**
     * @var MockObject
     */
    private $storeManager;

    /**
     * @var MockObject
     */
    private $eavConfig;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->resource = $this->createMock(ResourceConnection::class);
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->eavConfig = $this->createMock(\Magento\Eav\Model\Config::class);

        $this->model = $objectManager->getObject(
            Config::class,
            [
                'resource' => $this->resource,
                'storeManager' => $this->storeManager,
                'eavConfig' => $this->eavConfig,
            ]
        );

        parent::setUp();
    }

    public function testGetAttributesUsedForSortBy()
    {
        $expression = 'someExpression';
        $storeId = 1;
        $entityTypeId = 4;

        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $selectMock = $this->createMock(Select::class);
        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $entityTypeMock = $this->createMock(Type::class);

        $this->resource->expects($this->atLeastOnce())->method('getConnection')->willReturn($connectionMock);

        $connectionMock->expects($this->once())->method('getCheckSql')
            ->with('al.value IS NULL', 'main_table.frontend_label', 'al.value')
            ->willReturn($expression);
        $connectionMock->expects($this->atLeastOnce())->method('select')->willReturn($selectMock);

        $this->resource->expects($this->exactly(3))->method('getTableName')->withConsecutive(
            ['eav_attribute'],
            ['catalog_eav_attribute'],
            ['eav_attribute_label']
        )->willReturnOnConsecutiveCalls('eav_attribute', 'catalog_eav_attribute', 'eav_attribute_label');

        $this->storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getId')->willReturn($storeId);

        $this->eavConfig->expects($this->once())->method('getEntityType')->willReturn($entityTypeMock);
        $entityTypeMock->expects($this->once())->method('getId')->willReturn($entityTypeId);

        $selectMock->expects($this->once())->method('from')
            ->with(['main_table' => 'eav_attribute'])->willReturn($selectMock);
        $selectMock->expects($this->once())->method('join')->with(
            ['additional_table' => 'catalog_eav_attribute'],
            'main_table.attribute_id = additional_table.attribute_id'
        )->willReturn($selectMock);
        $selectMock->expects($this->once())->method('joinLeft')
            ->with(
                ['al' => 'eav_attribute_label'],
                'al.attribute_id = main_table.attribute_id AND al.store_id = ' . $storeId,
                ['store_label' => $expression]
            )->willReturn($selectMock);
        $selectMock->expects($this->exactly(2))->method('where')->withConsecutive(
            ['main_table.entity_type_id = ?', $entityTypeId],
            ['additional_table.used_for_sort_by = ?', 1]
        )->willReturn($selectMock);

        $connectionMock->expects($this->once())->method('fetchAll')->with($selectMock);

        $this->model->getAttributesUsedForSortBy();
    }
}

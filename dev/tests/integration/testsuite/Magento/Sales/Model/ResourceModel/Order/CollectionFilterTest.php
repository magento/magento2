<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Order;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFilter as Collection;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CollectionFilterTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Tests collection properties.
     *
     * @dataProvider getCollectionFiltersDataProvider
     * @throws \ReflectionException
     * @return void
     */
    public function testCollectionCreate($mainTable, $resourceModel): void
    {
        /** @var Collection $gridCollection */
        $gridCollection = $this->objectManager->create(
            Collection::class,
            [
                'mainTable' => $mainTable,
                'resourceModel' => $resourceModel
            ]
        );
        $tableDescription = $gridCollection->getConnection()
            ->describeTable($gridCollection->getMainTable());

        $mapper = new \ReflectionMethod(
            Collection::class,
            '_getMapper'
        );
        $mapper->setAccessible(true);
        $map = $mapper->invoke($gridCollection);

        self::assertIsArray($map);
        self::assertArrayHasKey('fields', $map);
        self::assertIsArray($map['fields']);
        self::assertCount(count($tableDescription), $map['fields']);

        foreach ($map['fields'] as $mappedName) {
            self::assertStringContainsString('main_table.', $mappedName);
        }
    }

    /**
     * Verifies that filter condition date is being converted to config timezone before select sql query
     *
     * @dataProvider getCollectionFiltersDataProvider
     * @return void
     */
    public function testAddFieldToFilter($mainTable, $resourceModel, $field): void
    {
        $filterDate = "2021-12-03 00:00:00";

        /** @var TimezoneInterface $timeZone */
        $timeZone = $this->objectManager->get(TimezoneInterface::class);
        /** @var Collection $gridCollection */
        $gridCollection = $this->objectManager->create(
            Collection::class,
            [
                'mainTable' => $mainTable,
                'resourceModel' => $resourceModel
            ]
        );

        $convertedDate = $timeZone->convertConfigTimeToUtc($filterDate);
        $collection = $gridCollection->addFieldToFilter($field, ['qteq' => $filterDate]);
        $expectedSelect = "SELECT `main_table`.* FROM `{$mainTable}` AS `main_table` " .
            "WHERE (((`main_table`.`{$field}` = '{$convertedDate}')))";

        $this->assertEquals($expectedSelect, $collection->getSelectSql(true));
    }

    /**
     * @return array
     */
    public function getCollectionFiltersDataProvider(): array
    {
        return [
            'shipment_grid_collection_for_created_at' => [
                'mainTable' => 'sales_shipment_grid',
                'resourceModel' => Shipment::class,
                'field' => 'created_at',
            ],
            'shipment_grid_collection_for_order_created_at' => [
                'mainTable' => 'sales_shipment_grid',
                'resourceModel' => Shipment::class,
                'field' => 'order_created_at',
            ],
            'creditmemo_grid_collection_for_created_at' => [
                'mainTable' => 'sales_creditmemo_grid',
                'resourceModel' => Creditmemo::class,
                'field' => 'created_at',
            ],
            'creditmemo_grid_collection_for_order_created_at' => [
                'mainTable' => 'sales_creditmemo_grid',
                'resourceModel' => Creditmemo::class,
                'field' => 'order_created_at',
            ]
        ];
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Order\Invoice\Grid;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\ResourceModel\Order\Invoice;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Grid\CollectionFilter as Collection;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CollectionFilterTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

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
     * @throws \ReflectionException
     * @return void
     */
    public function testCollectionCreate(): void
    {
        /** @var Collection $gridCollection */
        $gridCollection = $this->objectManager->create(
            Collection::class,
            [
                'mainTable' => 'sales_invoice_grid',
                'resourceModel' => Invoice::class
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
     * @return void
     */
    public function testAddFieldToFilter(): void
    {
        $filterDate = "2021-12-03 00:00:00";
        /** @var TimezoneInterface $timeZone */
        $timeZone = $this->objectManager->get(TimezoneInterface::class);
        /** @var Collection $gridCollection */
        $gridCollection = $this->objectManager->create(
            Collection::class,
            [
                'mainTable' => 'sales_invoice_grid',
                'resourceModel' => Invoice::class
            ]
        );
        $convertedDate = $timeZone->convertConfigTimeToUtc($filterDate);

        $collection = $gridCollection->addFieldToFilter('created_at', ['qteq' => $filterDate]);
        $expectedSelect = "SELECT `main_table`.* FROM `sales_invoice_grid` AS `main_table` " .
            "WHERE (((`main_table`.`created_at` = '{$convertedDate}')))";

        $this->assertEquals($expectedSelect, $collection->getSelectSql(true));
    }
}

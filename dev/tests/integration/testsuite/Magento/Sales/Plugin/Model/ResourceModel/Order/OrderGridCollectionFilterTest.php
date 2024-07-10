<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Plugin\Model\ResourceModel\Order;

use DateTimeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class OrderGridCollectionFilterTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var TimezoneInterface
     */
    private $timeZone;

    /**
     * @var OrderGridCollectionFilter
     */
    private $plugin;

    /**
     * @var SearchResult
     */
    private $searchResult;

    /**
     * @var \Closure
     */
    private $proceed;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->timeZone = $this->objectManager->get(TimezoneInterface::class);
        $this->proceed = function () {
            $this->proceed;
        };
        $this->plugin = $this->objectManager->create(
            OrderGridCollectionFilter::class,
            [
                'timezoneInterface' => $this->timeZone
            ]
        );
    }

    /**
     * Verifies that filter condition date is being converted to config timezone before select sql query
     *
     * @dataProvider \Magento\Sales\Plugin\Model\ResourceModel\Order\DataProvider\OrdersCollectionFilters::getCollectionFiltersDataProvider
     *
     * @param $mainTable
     * @param $resourceModel
     * @param $field
     * @param $fieldValue
     * @throws LocalizedException
     */
    public function testAroundAddFieldToFilter($mainTable, $resourceModel, $field, $fieldValue): void
    {
        $expectedSelect = "SELECT `main_table`.* FROM `{$mainTable}` AS `main_table` ";

        $convertedDate = $fieldValue instanceof DateTimeInterface
            ? $fieldValue->format('Y-m-d H:i:s') : $this->timeZone->convertConfigTimeToUtc($fieldValue);

        if ($mainTable == 'sales_order_grid') {
            $condition = ['from' => $fieldValue , 'locale' => "en_US", 'datetime' => true];
            $selectCondition = "WHERE (`{$field}` >= '{$convertedDate}')";
        } else {
            $condition = ['qteq' => $fieldValue];
            $selectCondition = "WHERE (((`{$field}` = '{$convertedDate}')))";
        }

        $this->searchResult = $this->objectManager->create(
            SearchResult::class,
            [
                'mainTable' => $mainTable,
                'resourceModel' => $resourceModel
            ]
        );
        $result = $this->plugin->aroundAddFieldToFilter(
            $this->searchResult,
            $this->proceed,
            $field,
            $condition
        );

        $this->assertEquals($expectedSelect . $selectCondition, $result->getSelectSql(true));
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Plugin\Model\ResourceModel\Order;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo;
use Magento\Sales\Model\ResourceModel\Order\Invoice;
use Magento\Sales\Model\ResourceModel\Order\Shipment;
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
     * @dataProvider getCollectionFiltersDataProvider
     * @param $mainTable
     * @param $resourceModel
     * @param $field
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testAroundAddFieldToFilter($mainTable, $resourceModel, $field): void
    {
        $filterDate = "2021-12-13 00:00:00";
        $convertedDate = $this->timeZone->convertConfigTimeToUtc($filterDate);

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
            ['qteq' => $filterDate]
        );

        $expectedSelect = "SELECT `main_table`.* FROM `{$mainTable}` AS `main_table` " .
            "WHERE (((`{$field}` = '{$convertedDate}')))";

        $this->assertEquals($expectedSelect, $result->getSelectSql(true));
    }

    /**
     * @return array
     */
    public function getCollectionFiltersDataProvider(): array
    {
        return [
            'invoice_grid_collection_for_created_at' => [
                'mainTable' => 'sales_invoice_grid',
                'resourceModel' => Invoice::class,
                'field' => 'created_at',
            ],
            'invoice_grid_collection_for_order_created_at' => [
                'mainTable' => 'sales_invoice_grid',
                'resourceModel' => Invoice::class,
                'field' => 'order_created_at',
            ],
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

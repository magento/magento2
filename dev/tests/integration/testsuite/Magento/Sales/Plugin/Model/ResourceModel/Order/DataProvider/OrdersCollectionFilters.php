<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Plugin\Model\ResourceModel\Order\DataProvider;

use DateTime;
use DateTimeZone;
use Exception;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order\Invoice;
use Magento\Sales\Model\ResourceModel\Order\Shipment;

class OrdersCollectionFilters
{

    /**
     * Return product attribute data set for update attribute options
     *
     * @return array
     * @throws Exception
     */
    public function getCollectionFiltersDataProvider(): array
    {
        $filterDate = "2021-12-13 00:00:00";
        $customerOrdersFilterDate = new DateTime($filterDate);
        $customerOrdersFilterDate->setTimezone(new DateTimeZone('UTC'));
        return [
            'invoice_grid_collection_for_created_at' => [
                'mainTable' => 'sales_invoice_grid',
                'resourceModel' => Invoice::class,
                'field' => 'created_at',
                'fieldValue' => $filterDate,
            ],
            'invoice_grid_collection_for_order_created_at' => [
                'mainTable' => 'sales_invoice_grid',
                'resourceModel' => Invoice::class,
                'field' => 'order_created_at',
                'fieldValue' => $filterDate,
            ],
            'shipment_grid_collection_for_created_at' => [
                'mainTable' => 'sales_shipment_grid',
                'resourceModel' => Shipment::class,
                'field' => 'created_at',
                'fieldValue' => $filterDate,
            ],
            'shipment_grid_collection_for_order_created_at' => [
                'mainTable' => 'sales_shipment_grid',
                'resourceModel' => Shipment::class,
                'field' => 'order_created_at',
                'fieldValue' => $filterDate,
            ],
            'creditmemo_grid_collection_for_created_at' => [
                'mainTable' => 'sales_creditmemo_grid',
                'resourceModel' => Creditmemo::class,
                'field' => 'created_at',
                'fieldValue' => $filterDate,
            ],
            'creditmemo_grid_collection_for_order_created_at' => [
                'mainTable' => 'sales_creditmemo_grid',
                'resourceModel' => Creditmemo::class,
                'field' => 'order_created_at',
                'fieldValue' => $filterDate,
            ],
            'customer_orders_grid_collection_for_order_created_at' => [
                'mainTable' => 'sales_order_grid',
                'resourceModel' => OrderCollection::class,
                'field' => 'created_at',
                'fieldValue' => $customerOrdersFilterDate,
            ],
        ];
    }
}

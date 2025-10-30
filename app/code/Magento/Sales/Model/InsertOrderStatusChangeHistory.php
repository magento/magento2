<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model;

use Magento\Sales\Model\ResourceModel\SalesOrderStatusChangeHistory
    as SalesOrderStatusChangeHistoryResourceModel;

class InsertOrderStatusChangeHistory
{
    /**
     * @param SalesOrderStatusChangeHistoryResourceModel $salesOrderStatusChangeHistoryResourceModel
     */
    public function __construct(
        private readonly SalesOrderStatusChangeHistoryResourceModel $salesOrderStatusChangeHistoryResourceModel,
    ) {
    }

    /**
     * Inserts latest status if status is changed
     *
     * @param Order $order
     * @return void
     */
    public function execute(Order $order): void
    {
        $latestStatus = $this->salesOrderStatusChangeHistoryResourceModel->getLatestStatus((int)$order->getId());
        if ((!$latestStatus && $order->getStatus()) ||
            (isset($latestStatus['status']) && $latestStatus['status'] !== $order->getStatus())
        ) {
            $this->salesOrderStatusChangeHistoryResourceModel->insert($order);
        }
    }
}

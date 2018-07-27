<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\CronJob;

use Magento\Store\Model\StoresConfig;
use Magento\Sales\Model\Order;

class CleanExpiredOrders
{
    /**
     * @var StoresConfig
     */
    protected $storesConfig;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param StoresConfig $storesConfig
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     */
    public function __construct(
        StoresConfig $storesConfig,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
    ) {
        $this->storesConfig = $storesConfig;
        $this->logger = $logger;
        $this->orderCollectionFactory = $collectionFactory;
    }

    /**
     * Clean expired quotes (cron process)
     *
     * @return void
     */
    public function execute()
    {
        $lifetimes = $this->storesConfig->getStoresConfigByPath('sales/orders/delete_pending_after');
        foreach ($lifetimes as $storeId => $lifetime) {
            /** @var $orders \Magento\Sales\Model\ResourceModel\Order\Collection */
            $orders = $this->orderCollectionFactory->create();
            $orders->addFieldToFilter('store_id', $storeId);
            $orders->addFieldToFilter('status', Order::STATE_PENDING_PAYMENT);
            $orders->getSelect()->where(
                new \Zend_Db_Expr('TIME_TO_SEC(TIMEDIFF(CURRENT_TIMESTAMP, `updated_at`)) >= ' . $lifetime * 60)
            );

            try {
                $orders->walk('cancel');
                $orders->walk('save');
            } catch (\Exception $e) {
                $this->logger->error('Error cancelling deprecated orders: ' . $e->getMessage());
            }
        }
    }
}

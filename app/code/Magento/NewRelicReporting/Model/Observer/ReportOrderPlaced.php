<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model\Observer;

use Magento\NewRelicReporting\Model\Config;

/**
 * Class ReportOrderPlaced
 */
class ReportOrderPlaced
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\NewRelicReporting\Model\OrdersFactory
     */
    protected $ordersFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * Constructor
     *
     * @param Config $config
     * @param \Magento\NewRelicReporting\Model\OrdersFactory $ordersFactory
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(
        Config $config,
        \Magento\NewRelicReporting\Model\OrdersFactory $ordersFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {
        $this->config = $config;
        $this->ordersFactory = $ordersFactory;
        $this->dateTime = $dateTime;
    }

    /**
     * Reports orders placed to the database reporting_orders table
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\NewRelicReporting\Model\Observer\ReportOrderPlaced
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->config->isNewRelicEnabled()) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $observer->getEvent()->getOrder();
            $itemCount = $order->getTotalItemCount();
            if (!is_numeric($itemCount) && empty($itemCount)) {
                $itemCount = $order->getTotalQtyOrdered();
            }

            $modelData = [
                'customer_id' => $order->getCustomerId(),
                'total' => $order->getGrandTotal(),
                'total_base' => $order->getBaseGrandTotal(),
                'item_count' => $itemCount,
                'updated_at' => $this->dateTime->formatDate(true)
            ];

            /** @var \Magento\NewRelicReporting\Model\Orders $orderModel */
            $orderModel = $this->ordersFactory->create();
            $orderModel->setData($modelData);
            $orderModel->save();
        }

        return $this;
    }
}

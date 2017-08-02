<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\NewRelicWrapper;

/**
 * Class ReportOrderPlacedToNewRelic
 * @since 2.0.0
 */
class ReportOrderPlacedToNewRelic implements ObserverInterface
{
    /**
     * @var Config
     * @since 2.0.0
     */
    protected $config;

    /**
     * @var NewRelicWrapper
     * @since 2.0.0
     */
    protected $newRelicWrapper;

    /**
     * @param Config $config
     * @param NewRelicWrapper $newRelicWrapper
     * @since 2.0.0
     */
    public function __construct(
        Config $config,
        NewRelicWrapper $newRelicWrapper
    ) {
        $this->config = $config;
        $this->newRelicWrapper = $newRelicWrapper;
    }

    /**
     * Reports orders placed to New Relic
     *
     * @param Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isNewRelicEnabled()) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $observer->getEvent()->getOrder();
            $itemCount = $order->getTotalItemCount();
            if (!is_numeric($itemCount) && empty($itemCount)) {
                $itemCount = $order->getTotalQtyOrdered();
            }

            $this->newRelicWrapper->addCustomParameter(Config::ORDER_PLACED, 1);
            $this->newRelicWrapper->addCustomParameter(Config::ORDER_ITEMS, $itemCount);
            $this->newRelicWrapper->addCustomParameter(Config::ORDER_VALUE, $order->getBaseGrandTotal());
        }
    }
}

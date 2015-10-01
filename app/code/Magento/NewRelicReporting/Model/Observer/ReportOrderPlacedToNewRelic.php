<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model\Observer;

use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\NewRelicWrapper;

/**
 * Class ReportOrderPlacedToNewRelic
 */
class ReportOrderPlacedToNewRelic
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var NewRelicWrapper
     */
    protected $newRelicWrapper;

    /**
     * Constructor
     *
     * @param Config $config
     * @param NewRelicWrapper $newRelicWrapper
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
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\NewRelicReporting\Model\Observer\ReportOrderPlacedToNewRelic
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

            $this->newRelicWrapper->addCustomParameter(Config::ORDER_PLACED, 1);
            $this->newRelicWrapper->addCustomParameter(Config::ORDER_ITEMS, $itemCount);
            $this->newRelicWrapper->addCustomParameter(Config::ORDER_VALUE, $order->getBaseGrandTotal());
        }

        return $this;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleQuoteTotalsObserver\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AfterCollectTotals implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $session;

    /**
     * @var \Magento\TestModuleQuoteTotalsObserver\Model\Config
     */
    private $config;

    /**
     * AfterCollectTotals constructor.
     * @param \Magento\Checkout\Model\Session $messageManager
     * @param \Magento\TestModuleQuoteTotalsObserver\Model\Config $config
     */
    public function __construct(
        \Magento\Checkout\Model\Session $messageManager,
        \Magento\TestModuleQuoteTotalsObserver\Model\Config $config
    ) {
        $this->config = $config;
        $this->session = $messageManager;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getEvent();
        if ($this->config->isActive()) {
            $this->session->getQuote();
        }
    }
}

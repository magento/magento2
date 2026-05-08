<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\NewRelicReporting\Model\NewRelicWrapper;

class DisableBrowserMonitoringAutomaticInjection implements ObserverInterface
{
    /**
     * @param NewRelicWrapper $newRelicWrapper
     */
    public function __construct(
        private readonly NewRelicWrapper $newRelicWrapper
    ) {
    }

    /**
     * Disables PHP agent's automatic injection of the browser monitoring in favor of manual injection
     *
     * New Relic's PHP agent does not support adding nonce attribute to the auto-injected scripts.
     * Thus, these scripts are now included out of box in the Http Response for compliance with CSP.
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if ($this->newRelicWrapper->isAutoInstrumentEnabled()) {
            $this->newRelicWrapper->disableAutorum();
        }
    }
}

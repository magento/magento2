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
 * Class ReportApplicationHandledExceptionToNewRelic
 */
class ReportApplicationHandledExceptionToNewRelic implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var NewRelicWrapper
     */
    private $newRelicWrapper;

    /**
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
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isNewRelicEnabled()) {
            $exception = $observer->getEvent()->getException();
            $this->newRelicWrapper->reportError($exception);
        }
    }
}

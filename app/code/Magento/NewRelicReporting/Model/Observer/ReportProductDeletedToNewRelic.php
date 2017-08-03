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
 * Class ReportProductDeletedToNewRelic
 * @since 2.0.0
 */
class ReportProductDeletedToNewRelic implements ObserverInterface
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
     * Reports any products deleted to New Relic
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isNewRelicEnabled()) {
            $this->newRelicWrapper->addCustomParameter(Config::PRODUCT_CHANGE, 'delete');
        }
    }
}

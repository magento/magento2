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
 * Class ReportProductSavedToNewRelic
 */
class ReportProductSavedToNewRelic implements ObserverInterface
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
     * Reports any products created or updated to New Relic
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isNewRelicEnabled()) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $observer->getEvent()->getProduct();
            if ($product->isObjectNew()) {
                $this->newRelicWrapper->addCustomParameter(
                    \Magento\NewRelicReporting\Model\Config::PRODUCT_CHANGE,
                    'create'
                );
            } else {
                $this->newRelicWrapper->addCustomParameter(
                    \Magento\NewRelicReporting\Model\Config::PRODUCT_CHANGE,
                    'update'
                );
            }
        }
    }
}

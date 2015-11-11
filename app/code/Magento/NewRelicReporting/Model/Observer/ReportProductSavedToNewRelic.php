<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model\Observer;

use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\NewRelicWrapper;

/**
 * Class ReportProductSavedToNewRelic
 */
class ReportProductSavedToNewRelic
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
     * Reports any products created or updated to New Relic
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\NewRelicReporting\Model\Observer\ReportProductSavedToNewRelic
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
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

        return $this;
    }
}

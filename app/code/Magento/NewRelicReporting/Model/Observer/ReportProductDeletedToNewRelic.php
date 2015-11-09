<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model\Observer;

use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\NewRelicWrapper;

/**
 * Class ReportProductDeletedToNewRelic
 */
class ReportProductDeletedToNewRelic
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
     * Reports any products deleted to New Relic
     *
     * @return \Magento\NewRelicReporting\Model\Observer\ReportProductDeletedToNewRelic
     */
    public function execute()
    {
        if ($this->config->isNewRelicEnabled()) {
            $this->newRelicWrapper->addCustomParameter(Config::PRODUCT_CHANGE, 'delete');
        }

        return $this;
    }
}

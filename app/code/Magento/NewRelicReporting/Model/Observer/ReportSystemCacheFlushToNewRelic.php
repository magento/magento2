<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model\Observer;

use Magento\NewRelicReporting\Model\Config;

/**
 * Class ReportSystemCacheFlushToNewRelic
 */
class ReportSystemCacheFlushToNewRelic
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    /**
     * @var \Magento\NewRelicReporting\Model\Apm\DeploymentsFactory
     */
    protected $deploymentsFactory;

    /**
     * Constructor
     *
     * @param Config $config
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param \Magento\NewRelicReporting\Model\Apm\DeploymentsFactory $deploymentsFactory
     */
    public function __construct(
        Config $config,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\NewRelicReporting\Model\Apm\DeploymentsFactory $deploymentsFactory
    ) {
        $this->config = $config;
        $this->backendAuthSession = $backendAuthSession;
        $this->deploymentsFactory = $deploymentsFactory;
    }

    /**
     * Report system cache is flushed to New Relic
     *
     * @return \Magento\NewRelicReporting\Model\Observer\ReportSystemCacheFlushToNewRelic
     */
    public function execute()
    {
        if ($this->config->isNewRelicEnabled()) {
            $user = $this->backendAuthSession->getUser();
            if ($user->getId()) {
                $this->deploymentsFactory->create()->setDeployment(
                    'Cache Flush',
                    $user->getUsername() . ' flushed the cache.',
                    $user->getUsername()
                );
            }
        }

        return $this;
    }
}

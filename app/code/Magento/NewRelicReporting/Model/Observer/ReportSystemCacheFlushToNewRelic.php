<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\NewRelicReporting\Model\Config;

/**
 * Class ReportSystemCacheFlushToNewRelic
 */
class ReportSystemCacheFlushToNewRelic implements ObserverInterface
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
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
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
    }
}

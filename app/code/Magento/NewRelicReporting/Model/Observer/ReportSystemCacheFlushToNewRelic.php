<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\NewRelicReporting\Model\Config;

/**
 * Class ReportSystemCacheFlushToNewRelic
 * @since 2.0.0
 */
class ReportSystemCacheFlushToNewRelic implements ObserverInterface
{
    /**
     * @var Config
     * @since 2.0.0
     */
    protected $config;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     * @since 2.0.0
     */
    protected $backendAuthSession;

    /**
     * @var \Magento\NewRelicReporting\Model\Apm\DeploymentsFactory
     * @since 2.0.0
     */
    protected $deploymentsFactory;

    /**
     * @param Config $config
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param \Magento\NewRelicReporting\Model\Apm\DeploymentsFactory $deploymentsFactory
     * @since 2.0.0
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
     * @since 2.0.0
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

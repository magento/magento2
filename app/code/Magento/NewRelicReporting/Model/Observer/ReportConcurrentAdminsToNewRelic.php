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
 * Class ReportConcurrentAdminsToNewRelic
 * @since 2.0.0
 */
class ReportConcurrentAdminsToNewRelic implements ObserverInterface
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
     * @var NewRelicWrapper
     * @since 2.0.0
     */
    protected $newRelicWrapper;

    /**
     * @param Config $config
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param NewRelicWrapper $newRelicWrapper
     * @since 2.0.0
     */
    public function __construct(
        Config $config,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        NewRelicWrapper $newRelicWrapper
    ) {
        $this->config = $config;
        $this->backendAuthSession = $backendAuthSession;
        $this->newRelicWrapper = $newRelicWrapper;
    }

    /**
     * Adds New Relic custom parameters per adminhtml request for current admin user, if applicable
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isNewRelicEnabled()) {
            if ($this->backendAuthSession->isLoggedIn()) {
                $user = $this->backendAuthSession->getUser();
                $this->newRelicWrapper->addCustomParameter(Config::ADMIN_USER_ID, $user->getId());
                $this->newRelicWrapper->addCustomParameter(Config::ADMIN_USER, $user->getUsername());
                $this->newRelicWrapper->addCustomParameter(
                    Config::ADMIN_NAME,
                    $user->getFirstname() . ' ' . $user->getLastname()
                );
            }
        }
    }
}

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
 * Class ReportConcurrentUsersToNewRelic
 * @since 2.0.0
 */
class ReportConcurrentUsersToNewRelic implements ObserverInterface
{
    /**
     * @var Config
     * @since 2.0.0
     */
    protected $config;

    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     * @since 2.0.0
     */
    protected $customerRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * @var NewRelicWrapper
     * @since 2.0.0
     */
    protected $newRelicWrapper;

    /**
     * @param Config $config
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param NewRelicWrapper $newRelicWrapper
     * @since 2.0.0
     */
    public function __construct(
        Config $config,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        NewRelicWrapper $newRelicWrapper
    ) {
        $this->config = $config;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->newRelicWrapper = $newRelicWrapper;
    }

    /**
     * Adds New Relic custom parameters per request for store, website, and logged in user if applicable
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isNewRelicEnabled()) {
            $this->newRelicWrapper->addCustomParameter(Config::STORE, $this->storeManager->getStore()->getName());
            $this->newRelicWrapper->addCustomParameter(Config::WEBSITE, $this->storeManager->getWebsite()->getName());

            if ($this->customerSession->isLoggedIn()) {
                $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
                $this->newRelicWrapper->addCustomParameter(Config::CUSTOMER_ID, $customer->getId());
                $this->newRelicWrapper->addCustomParameter(
                    Config::CUSTOMER_NAME,
                    $customer->getFirstname() . ' ' . $customer->getLastname()
                );
            }
        }
    }
}

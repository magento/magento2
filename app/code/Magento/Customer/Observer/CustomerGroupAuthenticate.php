<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Observer;

use Magento\Customer\Api\GroupExcludedWebsiteRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Customer group authenticate observer.
 */
class CustomerGroupAuthenticate implements ObserverInterface
{
    /**
     * @var GroupExcludedWebsiteRepositoryInterface
     */
    private $customerGroupExcludedWebsiteRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param GroupExcludedWebsiteRepositoryInterface $customerGroupExcludedWebsiteRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        GroupExcludedWebsiteRepositoryInterface $customerGroupExcludedWebsiteRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->customerGroupExcludedWebsiteRepository = $customerGroupExcludedWebsiteRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * Do not authenticate customer if website is excluded from customer's group.
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customer = $observer->getData('model');
        if ($customer->getGroupId()) {
            $excludedWebsites = $this->customerGroupExcludedWebsiteRepository->getCustomerGroupExcludedWebsites(
                (int)$customer->getGroupId()
            );
            if (in_array($websiteId, $excludedWebsites, true)) {
                throw new LocalizedException(__('This website is excluded from customer\'s group.'));
            }
        }
    }
}

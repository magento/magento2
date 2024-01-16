<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Observer\CatalogRule;

use Magento\Customer\Api\GroupExcludedWebsiteRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Add excluded customer group websites to catalog rule.
 */
class AddCustomerGroupExcludedWebsite implements ObserverInterface
{
    /**
     * @var GroupExcludedWebsiteRepositoryInterface
     */
    private $customerGroupExcludedWebsiteRepository;

    /**
     * @param GroupExcludedWebsiteRepositoryInterface $customerGroupExcludedWebsiteRepository
     */
    public function __construct(
        GroupExcludedWebsiteRepositoryInterface $customerGroupExcludedWebsiteRepository
    ) {
        $this->customerGroupExcludedWebsiteRepository = $customerGroupExcludedWebsiteRepository;
    }

    /**
     * Add excluded customer group websites to catalog rule as extension attributes.
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        $catalogRule = $observer->getData('catalog_rule');
        $rules = $catalogRule->getItems();
        if (!empty($rules)) {
            $allExcludedWebsiteIds = $this->customerGroupExcludedWebsiteRepository->getAllExcludedWebsites();
            if (!empty($allExcludedWebsiteIds)) {
                foreach ($rules as $rule) {
                    if ($rule->getIsActive()) {
                        $excludedWebsites = [];
                        $customerGroupIds = $rule->getCustomerGroupIds();
                        if (!empty($customerGroupIds)) {
                            foreach ($customerGroupIds as $customerGroupId) {
                                if (array_key_exists((int)$customerGroupId, $allExcludedWebsiteIds)) {
                                    $excludedWebsites[$customerGroupId] = $allExcludedWebsiteIds[(int)$customerGroupId];
                                }
                            }
                            if (!empty($excludedWebsites)) {
                                $ruleExtensionAttributes = $rule->getExtensionAttributes();
                                $ruleExtensionAttributes->setExcludeWebsiteIds($excludedWebsites);
                                $rule->setExtensionAttributes($ruleExtensionAttributes);
                            }
                        }
                    }
                }
            }
        }
    }
}

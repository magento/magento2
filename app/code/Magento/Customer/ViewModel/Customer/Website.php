<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Customer\ViewModel\Customer;

use Magento\Customer\Model\GroupManagement;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\System\Store as SystemStore;

/**
 * Customer's website view model
 */
class Website implements OptionSourceInterface
{
    /**
     * @var SystemStore
     */
    private $systemStore;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Store constructor.
     *
     * @param SystemStore $systemStore
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        SystemStore $systemStore,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->systemStore = $systemStore;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return $this->getWebsiteOptions();
    }

    /**
     * Adding group ID to options list
     *
     * @return array
     */
    private function getWebsiteOptions(): array
    {
        $options = $this->systemStore->getWebsiteValuesForForm();
        $defaultStoreViewIds = $this->getWebsiteDefaultStoreViewIds();
        foreach ($options as $key => $option) {
            $websiteId = $option['value'];
            $groupId = $this->scopeConfig->getValue(
                GroupManagement::XML_PATH_DEFAULT_ID,
                ScopeInterface::SCOPE_WEBSITE,
                $websiteId
            );
            $options[$key]['group_id'] = $groupId;
            $options[$key]['default_store_view_id'] = $defaultStoreViewIds[$websiteId] ?? null;
        }

        return $options;
    }

    /**
     * Build a map of website id => default store view id in a single pass
     *
     * @return array
     */
    private function getWebsiteDefaultStoreViewIds(): array
    {
        $defaultStoreViewIds = [];
        foreach ($this->systemStore->getWebsiteCollection() as $website) {
            $defaultStore = $website->getDefaultStore();
            $defaultStoreViewIds[$website->getId()] = $defaultStore ? $defaultStore->getId() : null;
        }

        return $defaultStoreViewIds;
    }
}

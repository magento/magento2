<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        foreach ($options as $key => $option) {
            $websiteId = $option['value'];
            $groupId = $this->scopeConfig->getValue(
                GroupManagement::XML_PATH_DEFAULT_ID,
                ScopeInterface::SCOPE_WEBSITE,
                $websiteId
            );
            $options[$key]['group_id'] = $groupId;
            $options[$key]['default_store_view_id'] = $this->getWebsiteDefaultStoreViewId($websiteId);
        }

        return $options;
    }

    /**
     * Get Default store view id by Website id
     *
     * @param string $websiteId
     * @return mixed
     */
    private function getWebsiteDefaultStoreViewId($websiteId)
    {
        $defaultStoreViewId = null;
        $websites = $this->systemStore->getWebsiteCollection();

        foreach ($websites as $website) {
            if ($website->getId() === $websiteId) {
                $defaultStore = $website->getDefaultStore();
                // Check if the default store exist
                if ($defaultStore) {
                    $defaultStoreViewId = $defaultStore->getId();
                }
                break;
            }
        }

        return $defaultStoreViewId;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Plugin;

use Magento\Customer\Model\Config\Share;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AllowedCountries
 * @since 2.2.0
 */
class AllowedCountries
{
    /**
     * @var \Magento\Customer\Model\Config\Share
     * @since 2.2.0
     */
    private $shareConfig;

    /**
     * @var StoreManagerInterface
     * @since 2.2.0
     */
    private $storeManager;

    /**
     * @param Share $share
     * @param StoreManagerInterface $storeManager
     * @since 2.2.0
     */
    public function __construct(
        Share $share,
        StoreManagerInterface $storeManager
    ) {
        $this->shareConfig = $share;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve all allowed countries or specific by scope depends on customer share setting
     *
     * @param \Magento\Directory\Model\AllowedCountries $subject
     * @param string | null $filter
     * @param string $scope
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function beforeGetAllowedCountries(
        \Magento\Directory\Model\AllowedCountries $subject,
        $scope = ScopeInterface::SCOPE_WEBSITE,
        $scopeCode = null
    ) {
        if ($this->shareConfig->isGlobalScope()) {
            //Check if we have shared accounts - than merge all website allowed countries
            $scopeCode = array_map(function (WebsiteInterface $website) {
                return $website->getId();
            }, $this->storeManager->getWebsites());
            $scope = ScopeInterface::SCOPE_WEBSITES;
        }

        return [$scope, $scopeCode];
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerPageCache\Plugin\PageCache\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\LoginAsCustomerApi\Api\GetLoggedAsCustomerAdminIdInterface;
use Magento\PageCache\Model\Config;
use Magento\Store\Model\ScopeInterface;

/**
 * Disable PageCache if enabled corresponding option in configuration
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class DisablePageCacheIfNeededPlugin
{
    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var GetLoggedAsCustomerAdminIdInterface
     */
    private $getLoggedAsCustomerAdminId;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param GetLoggedAsCustomerAdminIdInterface $getLoggedAsCustomerAdminId
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        GetLoggedAsCustomerAdminIdInterface $getLoggedAsCustomerAdminId
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->getLoggedAsCustomerAdminId = $getLoggedAsCustomerAdminId;
    }

    /**
     * Disable page cache if needed when admin is logged as customer
     *
     * @param Config $subject
     * @param bool $isEnabled
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsEnabled(Config $subject, $isEnabled): bool
    {
        if ($isEnabled) {
            $disable = $this->scopeConfig->isSetFlag(
                'login_as_customer/general/disable_page_cache',
                ScopeInterface::SCOPE_STORE
            );
            $adminId = $this->getLoggedAsCustomerAdminId->execute();
            if ($disable && $adminId) {
                $isEnabled = false;
            }
        }
        return $isEnabled;
    }
}

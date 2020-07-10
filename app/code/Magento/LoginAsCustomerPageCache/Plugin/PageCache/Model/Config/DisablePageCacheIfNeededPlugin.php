<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerPageCache\Plugin\PageCache\Model\Config;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
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
     * @var Session
     */
    private $customerSession;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $customerSession
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Session $customerSession
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
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
            $disable = $this->scopeConfig->getValue(
                'login_as_customer/general/disable_page_cache',
                ScopeInterface::SCOPE_STORE
            );
            $adminId = $this->customerSession->getLoggedAsCustomerAdmindId();
            if ($disable && $adminId) {
                $isEnabled = false;
            }
        }
        return $isEnabled;
    }
}

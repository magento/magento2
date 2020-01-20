<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model\PageCache;

/**
 * Page cache config plugin
 */
class ConfigPlugin
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $_customerSession;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_customerSession = $customerSession;
    }

    /**
     * Disable page cache if needed when admin is logged as customer
     *
     * @param \Magento\PageCache\Model\Config $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsEnabled(\Magento\PageCache\Model\Config $subject, $result): bool
    {
        if ($result) {
            $disable = $this->_scopeConfig->getValue(
                'mfloginascustomer/general/disable_page_cache',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $adminId = $this->_customerSession->getLoggedAsCustomerAdmindId();
            if ($disable && $adminId) {
                $result = false;
            }
        }

        return $result;
    }
}

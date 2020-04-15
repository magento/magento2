<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\LoginAsCustomer\Model\PageCache;

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
    protected $_scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

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
    public function afterIsEnabled(\Magento\PageCache\Model\Config $subject, $result)
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

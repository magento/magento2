<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\Helper;

/**
 * App config data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Store cache context
     */
    const CONTEXT_STORE = 'store';

    /**#@+
     * Paths for various config settings
     */
    const XML_PATH_DEV_ALLOW_IPS = 'dev/restrict/allow_ips';
    /**#@- */

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isDevAllowed($storeId = null)
    {
        $allow = true;

        $allowedIps = $this->_scopeConfig->getValue(
            self::XML_PATH_DEV_ALLOW_IPS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $remoteAddr = $this->_remoteAddress->getRemoteAddress();
        if (!empty($allowedIps) && !empty($remoteAddr)) {
            $allowedIps = preg_split('#\s*,\s*#', $allowedIps, null, PREG_SPLIT_NO_EMPTY);
            if (array_search($remoteAddr, $allowedIps) === false
                && array_search($this->_httpHeader->getHttpHost(), $allowedIps) === false
            ) {
                $allow = false;
            }
        }

        return $allow;
    }
}

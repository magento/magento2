<?php
/**
 * Url security information
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\Url;

class SecurityInfo implements \Magento\Framework\Url\SecurityInfoInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * List of secure url patterns
     *
     * @var array
     */
    protected $_secureUrlList = [];

    /**
     * List of already checked urls
     *
     * @var array
     */
    protected $_secureUrlCache = [];

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $secureUrlList
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $secureUrlList = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_secureUrlList = $secureUrlList;
    }

    /**
     * Check whether url is secure
     *
     * @param string $url
     * @return bool
     */
    public function isSecure($url)
    {
        if (!$this->_scopeConfig->getValue(
            \Magento\Store\Model\Store::XML_PATH_SECURE_IN_FRONTEND,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
            return false;
        }

        if (!isset($this->_secureUrlCache[$url])) {
            $this->_secureUrlCache[$url] = false;
            foreach ($this->_secureUrlList as $match) {
                if (strpos($url, (string)$match) === 0) {
                    $this->_secureUrlCache[$url] = true;
                    break;
                }
            }
        }
        return $this->_secureUrlCache[$url];
    }
}

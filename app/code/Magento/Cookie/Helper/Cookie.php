<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cookie\Helper;

/**
 * Cookie helper
 * @api
 * @since 2.0.0
 */
class Cookie extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Cookie name for users who allowed cookie save
     */
    const IS_USER_ALLOWED_SAVE_COOKIE = 'user_allowed_save_cookie';

    /**
     * Path to configuration, check is enable cookie restriction mode
     */
    const XML_PATH_COOKIE_RESTRICTION = 'web/cookie/cookie_restriction';

    /**
     * Cookie restriction lifetime configuration path
     */
    const XML_PATH_COOKIE_RESTRICTION_LIFETIME = 'web/cookie/cookie_restriction_lifetime';

    /**
     * @var \Magento\Store\Model\Store
     * @since 2.0.0
     */
    protected $_currentStore;

    /**
     * @var \Magento\Store\Model\Website
     * @since 2.0.0
     */
    protected $_website;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     *
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context);
        $this->_currentStore = isset($data['current_store']) ? $data['current_store'] : $storeManager->getStore();

        if (!$this->_currentStore instanceof \Magento\Store\Model\Store) {
            throw new \InvalidArgumentException('Required store object is invalid');
        }

        $this->_website = isset($data['website']) ? $data['website'] : $storeManager->getWebsite();

        if (!$this->_website instanceof \Magento\Store\Model\Website) {
            throw new \InvalidArgumentException('Required website object is invalid');
        }
    }

    /**
     * Check if cookie restriction notice should be displayed
     *
     * @return bool
     * @since 2.0.0
     */
    public function isUserNotAllowSaveCookie()
    {
        $acceptedSaveCookiesWebsites = $this->_getAcceptedSaveCookiesWebsites();
        return $this->isCookieRestrictionModeEnabled() &&
            empty($acceptedSaveCookiesWebsites[$this->_website->getId()]);
    }

    /**
     * Check if cookie restriction mode is enabled for this store
     *
     * @return bool
     * @since 2.2.0
     */
    public function isCookieRestrictionModeEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COOKIE_RESTRICTION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_currentStore
        );
    }

    /**
     * Return serialized list of accepted save cookie website
     *
     * @return string
     * @since 2.0.0
     */
    public function getAcceptedSaveCookiesWebsiteIds()
    {
        $acceptedSaveCookiesWebsites = $this->_getAcceptedSaveCookiesWebsites();
        $acceptedSaveCookiesWebsites[(int)$this->_website->getId()] = 1;
        return json_encode($acceptedSaveCookiesWebsites);
    }

    /**
     * Get accepted save cookies websites
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getAcceptedSaveCookiesWebsites()
    {
        $unSerializedList = null;
        $serializedList = $this->_request->getCookie(self::IS_USER_ALLOWED_SAVE_COOKIE, false);
        if ($serializedList) {
            $unSerializedList = json_decode($serializedList, true);
        }
        return is_array($unSerializedList) ? $unSerializedList : [];
    }

    /**
     * Get cookie restriction lifetime (in seconds)
     *
     * @return int
     * @since 2.0.0
     */
    public function getCookieRestrictionLifetime()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_COOKIE_RESTRICTION_LIFETIME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_currentStore
        );
    }
}

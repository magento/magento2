<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Core
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Helper;

/**
 * Core Cookie helper
 */
class Cookie extends \Magento\App\Helper\AbstractHelper
{
    /**
     * Cookie name for users who allowed cookie save
     */
    const IS_USER_ALLOWED_SAVE_COOKIE  = 'user_allowed_save_cookie';

    /**
     * Path to configuration, check is enable cookie restriction mode
     */
    const XML_PATH_COOKIE_RESTRICTION  = 'web/cookie/cookie_restriction';

    /**
     * Cookie restriction lifetime configuration path
     */
    const XML_PATH_COOKIE_RESTRICTION_LIFETIME = 'web/cookie/cookie_restriction_lifetime';

    /**
     * @var \Magento\Core\Model\Store
     */
    protected $_currentStore;

    /**
     * @var \Magento\Core\Model\Website
     */
    protected $_website;

    /**
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param array $data
     * 
     * @throws \InvalidArgumentException
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        array $data = array()
    ) {
        parent::__construct($context);
        $this->_currentStore = isset($data['current_store']) ? $data['current_store'] : $storeManager->getStore();

        if (!$this->_currentStore instanceof \Magento\Core\Model\Store) {
            throw new \InvalidArgumentException('Required store object is invalid');
        }

        $this->_website = isset($data['website']) ? $data['website'] : $storeManager->getWebsite();

        if (!$this->_website instanceof \Magento\Core\Model\Website) {
            throw new \InvalidArgumentException('Required website object is invalid');
        }
    }


    /**
     * Check if cookie restriction notice should be displayed
     *
     * @return bool
     */
    public function isUserNotAllowSaveCookie()
    {
        $acceptedSaveCookiesWebsites = $this->_getAcceptedSaveCookiesWebsites();
        return $this->_currentStore->getConfig(self::XML_PATH_COOKIE_RESTRICTION)
            && empty($acceptedSaveCookiesWebsites[$this->_website->getId()]);
    }

    /**
     * Return serialized list of accepted save cookie website
     *
     * @return string
     */
    public function getAcceptedSaveCookiesWebsiteIds()
    {
        $acceptedSaveCookiesWebsites = $this->_getAcceptedSaveCookiesWebsites();
        $acceptedSaveCookiesWebsites[$this->_website->getId()] = 1;
        return json_encode($acceptedSaveCookiesWebsites);
    }

    /**
     * Get accepted save cookies websites
     *
     * @return array
     */
    protected function _getAcceptedSaveCookiesWebsites()
    {
        $serializedList = $this->_request->getCookie(self::IS_USER_ALLOWED_SAVE_COOKIE, false);
        $unSerializedList = json_decode($serializedList, true);
        return is_array($unSerializedList) ? $unSerializedList : array();
    }

    /**
     * Get cookie restriction lifetime (in seconds)
     *
     * @return int
     */
    public function getCookieRestrictionLifetime()
    {
        return (int) $this->_currentStore->getConfig(self::XML_PATH_COOKIE_RESTRICTION_LIFETIME);
    }
}

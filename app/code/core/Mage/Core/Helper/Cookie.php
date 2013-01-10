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
 * @category    Mage
 * @package     Mage_Core
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Core Cookie helper
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Helper_Cookie extends Mage_Core_Helper_Abstract
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
     * @var Mage_Core_Model_Store
     */
    protected $_currentStore;

    /**
     * @var Mage_Core_Model_Cookie
     */
    protected $_cookieModel;

    /**
     * @var Mage_Core_Model_Website
     */
    protected $_website;

    /**
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->_currentStore = isset($data['current_store']) ? $data['current_store'] : Mage::app()->getStore();

        if (!($this->_currentStore instanceof Mage_Core_Model_Store)) {
            throw new InvalidArgumentException('Required store object is invalid');
        }

        $this->_cookieModel = isset($data['cookie_model'])
            ? $data['cookie_model'] : Mage::getSingleton('Mage_Core_Model_Cookie');

        if (false == ($this->_cookieModel instanceof Mage_Core_Model_Cookie)) {
            throw new InvalidArgumentException('Required cookie object is invalid');
        }

        $this->_website = isset($data['website']) ? $data['website'] : Mage::app()->getWebsite();

        if (false == ($this->_website instanceof Mage_Core_Model_Website)) {
            throw new InvalidArgumentException('Required website object is invalid');
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
        return $this->_currentStore->getConfig(self::XML_PATH_COOKIE_RESTRICTION) &&
            empty($acceptedSaveCookiesWebsites[$this->_website->getId()]);
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
        $serializedList = $this->_cookieModel->get(self::IS_USER_ALLOWED_SAVE_COOKIE);
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
        return (int)$this->_currentStore->getConfig(self::XML_PATH_COOKIE_RESTRICTION_LIFETIME);
    }
}

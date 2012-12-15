<?php
/**
 * Web API User model.
 *
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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @method Mage_Webapi_Model_Acl_User setRoleId() setRoleId(int $id)
 * @method int getRoleId() getRoleId()
 * @method Mage_Webapi_Model_Acl_User setApiKey() setApiKey(string $apiKey)
 * @method string getApiKey() getApiKey()
 * @method Mage_Webapi_Model_Acl_User setContactEmail() setContactEmail(string $contactEmail)
 * @method string getContactEmail() getContactEmail()
 * @method Mage_Webapi_Model_Acl_User setSecret() setSecret(string $secret)
 * @method Mage_Webapi_Model_Acl_User setCompanyName() setCompanyName(string $companyName)
 * @method string getCompanyName() getCompanyName()
 */
class Mage_Webapi_Model_Acl_User extends Mage_Core_Model_Abstract implements Mage_Oauth_Model_ConsumerInterface
{
    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'webapi_user';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Mage_Webapi_Model_Resource_Acl_User');
    }

    /**
     * Get role users.
     *
     * @param integer $roleId
     * @return array
     */
    public function getRoleUsers($roleId)
    {
        return $this->getResource()->getRoleUsers($roleId);
    }

    /**
     * Load user by key.
     *
     * @param string $key
     * @return Mage_Webapi_Model_Acl_User
     */
    public function loadByKey($key)
    {
        return $this->load($key, 'api_key');
    }

    /**
     * Get consumer key.
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->getData('secret');
    }

    /**
     * Get consumer callback URL.
     *
     * @return string
     */
    public function getCallBackUrl()
    {
         return '';
    }
}

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
 * @package     Mage_GoogleShopping
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Google Content Item Types Model
 *
 * @category   Mage
 * @package    Mage_GoogleShopping
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleShopping_Model_Service extends Varien_Object
{
    /**
     * Client instance identifier in registry
     *
     * @var string
     */
    protected $_clientRegistryId = 'GCONTENT_HTTP_CLIENT';

    /**
     * Retutn Google Content Client Instance
     *
     * @param int $storeId
     * @param string $loginToken
     * @param string $loginCaptcha
     * @return Zend_Http_Client
     */
    public function getClient($storeId = null, $loginToken = null, $loginCaptcha = null)
    {
        $user = $this->getConfig()->getAccountLogin($storeId);
        $pass = $this->getConfig()->getAccountPassword($storeId);
        $type = $this->getConfig()->getAccountType($storeId);

        // Create an authenticated HTTP client
        $errorMsg = Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Sorry, but we can\'t connect to Google Content. Please check the account settings in your store configuration.');
        try {
            if (!Mage::registry($this->_clientRegistryId)) {
                $client = Zend_Gdata_ClientLogin::getHttpClient($user, $pass,
                    Varien_Gdata_Gshopping_Content::AUTH_SERVICE_NAME, null, '', $loginToken, $loginCaptcha,
                    Zend_Gdata_ClientLogin::CLIENTLOGIN_URI, $type
                );
                $configTimeout = array('timeout' => 60);
                $client->setConfig($configTimeout);
                Mage::register($this->_clientRegistryId, $client);
            }
        } catch (Zend_Gdata_App_CaptchaRequiredException $e) {
            throw $e;
        } catch (Zend_Gdata_App_HttpException $e) {
            Mage::throwException($errorMsg . Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Error: %s', $e->getMessage()));
        } catch (Zend_Gdata_App_AuthException $e) {
            Mage::throwException($errorMsg . Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Error: %s', $e->getMessage()));
        }

        return Mage::registry($this->_clientRegistryId);
    }

    /**
     * Set Google Content Client Instance
     *
     * @param Zend_Http_Client $client
     * @return Mage_GoogleShopping_Model_Service
     */
    public function setClient($client)
    {
        Mage::unregister($this->_clientRegistryId);
        Mage::register($this->_clientRegistryId, $client);
        return $this;
    }

    /**
     * Return Google Content Service Instance
     *
     * @param int $storeId
     * @return Varien_Gdata_Gshopping_Content
     */
    public function getService($storeId = null)
    {
        if (!$this->_service) {
            $this->_service = $this->_connect($storeId);

            if ($this->getConfig()->getIsDebug($storeId)) {
                $this->_service
                    ->setLogAdapter(Mage::getModel('Mage_Core_Model_Log_Adapter',
                    array('fileName' => 'googleshopping.log')), 'log')
                    ->setDebug(true);
            }
        }
        return $this->_service;
    }

    /**
     * Set Google Content Service Instance
     *
     * @param Varien_Gdata_Gshopping_Content $service
     * @return Mage_GoogleShopping_Model_Service
     */
    public function setService($service)
    {
        $this->_service = $service;
        return $this;
    }

    /**
     * Google Content Config
     *
     * @return Mage_GoogleShopping_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('Mage_GoogleShopping_Model_Config');
    }

    /**
     * Authorize Google Account
     *
     * @param int $storeId
     * @return Varien_Gdata_Gshopping_Content service
     */
    protected function _connect($storeId = null)
    {
        $accountId = $this->getConfig()->getAccountId($storeId);
        $client = $this->getClient($storeId);
        $service = new Varien_Gdata_Gshopping_Content($client, $accountId);
        return $service;
    }
}

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
 * @package     Mage_Log
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Enter description here ...
 *
 * @method Mage_Log_Model_Resource_Visitor _getResource()
 * @method Mage_Log_Model_Resource_Visitor getResource()
 * @method string getSessionId()
 * @method Mage_Log_Model_Visitor setSessionId(string $value)
 * @method Mage_Log_Model_Visitor setFirstVisitAt(string $value)
 * @method Mage_Log_Model_Visitor setLastVisitAt(string $value)
 * @method int getLastUrlId()
 * @method Mage_Log_Model_Visitor setLastUrlId(int $value)
 * @method int getStoreId()
 * @method Mage_Log_Model_Visitor setStoreId(int $value)
 *
 * @category    Mage
 * @package     Mage_Log
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Log_Model_Visitor extends Mage_Core_Model_Abstract
{
    const DEFAULT_ONLINE_MINUTES_INTERVAL = 15;
    const VISITOR_TYPE_CUSTOMER = 'c';
    const VISITOR_TYPE_VISITOR  = 'v';

    protected $_skipRequestLogging = false;

    /**
     * Onject initialization
     */
    protected function _construct()
    {
        $this->_init('Mage_Log_Model_Resource_Visitor');
        $userAgent = Mage::helper('Mage_Core_Helper_Http')->getHttpUserAgent();
        $ignoreAgents = Mage::getConfig()->getNode('global/ignore_user_agents');
        if ($ignoreAgents) {
            $ignoreAgents = $ignoreAgents->asArray();
            if (in_array($userAgent, $ignoreAgents)) {
                $this->_skipRequestLogging = true;
            }
        }
    }

    /**
     * Retrieve session object
     *
     * @return Mage_Core_Model_Session_Abstract
     */
    protected function _getSession()
    {
        return Mage::getSingleton('Mage_Core_Model_Session');
    }

    /**
     * Initialize visitor information from server data
     *
     * @return Mage_Log_Model_Visitor
     */
    public function initServerData()
    {
        /* @var $helper Mage_Core_Helper_Http */
        $helper = Mage::helper('Mage_Core_Helper_Http');

        $this->addData(array(
            'server_addr'           => $helper->getServerAddr(true),
            'remote_addr'           => $helper->getRemoteAddr(true),
            'http_secure'           => Mage::app()->getStore()->isCurrentlySecure(),
            'http_host'             => $helper->getHttpHost(true),
            'http_user_agent'       => $helper->getHttpUserAgent(true),
            'http_accept_language'  => $helper->getHttpAcceptLanguage(true),
            'http_accept_charset'   => $helper->getHttpAcceptCharset(true),
            'request_uri'           => $helper->getRequestUri(true),
            'session_id'            => $this->_getSession()->getSessionId(),
            'http_referer'          => $helper->getHttpReferer(true),
        ));

        return $this;
    }

    /**
     * Return Online Minutes Interval
     *
     * @return int Minutes Interval
     */
    public static function getOnlineMinutesInterval()
    {
        $configValue = Mage::getStoreConfig('customer/online_customers/online_minutes_interval');
        return intval($configValue) > 0
            ? intval($configValue)
            : self::DEFAULT_ONLINE_MINUTES_INTERVAL;
    }

    /**
     * Retrieve url from model data
     *
     * @return string
     */
    public function getUrl()
    {
        $url = 'http' . ($this->getHttpSecure() ? 's' : '') . '://';
        $url .= $this->getHttpHost().$this->getRequestUri();
        return $url;
    }

    public function getFirstVisitAt()
    {
        if (!$this->hasData('first_visit_at')) {
            $this->setData('first_visit_at', now());
        }
        return $this->getData('first_visit_at');
    }

    public function getLastVisitAt()
    {
        if (!$this->hasData('last_visit_at')) {
            $this->setData('last_visit_at', now());
        }
        return $this->getData('last_visit_at');
    }

    /**
     * Initialization visitor information by request
     *
     * Used in event "controller_action_predispatch"
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_Log_Model_Visitor
     */
    public function initByRequest($observer)
    {
        if ($this->_skipRequestLogging || $this->isModuleIgnored($observer)) {
            return $this;
        }

        $this->setData($this->_getSession()->getVisitorData());
        $this->initServerData();

        if (!$this->getId()) {
            $this->setFirstVisitAt(now());
            $this->setIsNewVisitor(true);
            $this->save();
        }
        return $this;
    }

    /**
     * Saving visitor information by request
     *
     * Used in event "controller_action_postdispatch"
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_Log_Model_Visitor
     */
    public function saveByRequest($observer)
    {
        if ($this->_skipRequestLogging || $this->isModuleIgnored($observer)) {
            return $this;
        }

        try {
            $this->setLastVisitAt(now());
            $this->save();
            $this->_getSession()->setVisitorData($this->getData());
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    /**
     * Bind customer data when customer login
     *
     * Used in event "customer_login"
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_Log_Model_Visitor
     */
    public function bindCustomerLogin($observer)
    {
        if (!$this->getCustomerId() && $customer = $observer->getEvent()->getCustomer()) {
            $this->setDoCustomerLogin(true);
            $this->setCustomerId($customer->getId());
        }
        return $this;
    }

    /**
     * Bind customer data when customer logout
     *
     * Used in event "customer_logout"
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_Log_Model_Visitor
     */
    public function bindCustomerLogout($observer)
    {
        if ($this->getCustomerId() && $customer = $observer->getEvent()->getCustomer()) {
            $this->setDoCustomerLogout(true);
        }
        return $this;
    }

    public function bindQuoteCreate($observer)
    {
        if ($quote = $observer->getEvent()->getQuote()) {
            if ($quote->getIsCheckoutCart()) {
                $this->setQuoteId($quote->getId());
                $this->setDoQuoteCreate(true);
            }
        }
        return $this;
    }

    public function bindQuoteDestroy($observer)
    {
        if ($quote = $observer->getEvent()->getQuote()) {
            $this->setDoQuoteDestroy(true);
        }
        return $this;
    }

    /**
     * Methods for research (depends from customer online admin section)
     */
    public function addIpData($data)
    {
        $ipData = array();
        $data->setIpData($ipData);
        return $this;
    }

    public function addCustomerData($data)
    {
        $customerId = $data->getCustomerId();
        if( intval($customerId) <= 0 ) {
            return $this;
        }
        $customerData = Mage::getModel('Mage_Customer_Model_Customer')->load($customerId);
        $newCustomerData = array();
        foreach( $customerData->getData() as $propName => $propValue ) {
            $newCustomerData['customer_' . $propName] = $propValue;
        }

        $data->addData($newCustomerData);
        return $this;
    }

    public function addQuoteData($data)
    {
        $quoteId = $data->getQuoteId();
        if( intval($quoteId) <= 0 ) {
            return $this;
        }
        $data->setQuoteData(Mage::getModel('Mage_Sales_Model_Quote')->load($quoteId));
        return $this;
    }

    public function isModuleIgnored($observer)
    {
        $ignores = Mage::getConfig()->getNode('global/ignoredModules/entities')->asArray();

        if( is_array($ignores) && $observer) {
            $curModule = $observer->getEvent()->getControllerAction()->getRequest()->getRouteName();
            if (isset($ignores[$curModule])) {
                return true;
            }
        }
        return false;
    }
}

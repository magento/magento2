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
 * @package     Magento_Log
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * @method \Magento\Log\Model\Resource\Visitor _getResource()
 * @method \Magento\Log\Model\Resource\Visitor getResource()
 * @method string getSessionId()
 * @method \Magento\Log\Model\Visitor setSessionId(string $value)
 * @method \Magento\Log\Model\Visitor setFirstVisitAt(string $value)
 * @method \Magento\Log\Model\Visitor setLastVisitAt(string $value)
 * @method int getLastUrlId()
 * @method \Magento\Log\Model\Visitor setLastUrlId(int $value)
 * @method int getStoreId()
 * @method \Magento\Log\Model\Visitor setStoreId(int $value)
 *
 * @category    Magento
 * @package     Magento_Log
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Log\Model;

class Visitor extends \Magento\Core\Model\AbstractModel
{
    const DEFAULT_ONLINE_MINUTES_INTERVAL = 15;
    const VISITOR_TYPE_CUSTOMER = 'c';
    const VISITOR_TYPE_VISITOR  = 'v';

    /**
     * @var bool
     */
    protected $_skipRequestLogging = false;

    /**
     * Core http
     *
     * @var \Magento\Core\Helper\Http
     */
    protected $_coreHttp = null;

    /**
     * @var array
     */
    protected $_ignoredUserAgents;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Core\Model\Config
     */
    protected $_coreConfig;

    /**
     * Ignored Modules
     *
     * @var array
     */
    protected $_ignores;

    /*
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Core\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Sales\Model\QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @param \Magento\Core\Model\Context               $context
     * @param \Magento\Core\Model\Registry              $registry
     * @param \Magento\Core\Model\Store\Config          $coreStoreConfig
     * @param \Magento\Event\ManagerInterface         $eventManager
     * @param \Magento\Customer\Model\CustomerFactory   $customerFactory
     * @param \Magento\Sales\Model\QuoteFactory         $quoteFactory
     * @param \Magento\Core\Model\Session               $session
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Helper\Http                 $coreHttp
     * @param \Magento\Core\Model\Config                $coreConfig
     * @param array                                    $data
     * @param array                                    $ignoredUserAgents
     * @param array                                    $ignores
     * @param \Magento\Core\Model\Resource\AbstractResource     $resource
     * @param \Magento\Data\Collection\Db               $resourceCollection
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Sales\Model\QuoteFactory $quoteFactory,
        \Magento\Core\Model\Session $session,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Helper\Http $coreHttp,
        \Magento\Core\Model\Config $coreConfig,
        array $data = array(),
        array $ignoredUserAgents = array(),
        array $ignores = array(),
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null
    ) {
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_eventManager = $eventManager;
        $this->_customerFactory = $customerFactory;
        $this->_quoteFactory = $quoteFactory;
        $this->_session = $session;
        $this->_storeManager = $storeManager;
        $this->_coreHttp = $coreHttp;
        $this->_coreConfig = $coreConfig;
        $this->_ignoredUserAgents = $ignoredUserAgents;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_ignores = $ignores;
    }

    /**
     * Object initialization
     */
    protected function _construct()
    {
        $this->_init('Magento\Log\Model\Resource\Visitor');
        $userAgent = $this->_coreHttp->getHttpUserAgent();
        if ($this->_ignoredUserAgents) {
            if (in_array($userAgent, $this->_ignoredUserAgents)) {
                $this->_skipRequestLogging = true;
            }
        }
    }

    /**
     * Retrieve session object
     *
     * @return \Magento\Core\Model\Session\AbstractSession
     */
    protected function _getSession()
    {
        return $this->_session;
    }

    /**
     * Initialize visitor information from server data
     *
     * @return \Magento\Log\Model\Visitor
     */
    public function initServerData()
    {
        $this->addData(array(
            'server_addr'           => $this->_coreHttp->getServerAddr(true),
            'remote_addr'           => $this->_coreHttp->getRemoteAddr(true),
            'http_secure'           => $this->_storeManager->getStore()->isCurrentlySecure(),
            'http_host'             => $this->_coreHttp->getHttpHost(true),
            'http_user_agent'       => $this->_coreHttp->getHttpUserAgent(true),
            'http_accept_language'  => $this->_coreHttp->getHttpAcceptLanguage(true),
            'http_accept_charset'   => $this->_coreHttp->getHttpAcceptCharset(true),
            'request_uri'           => $this->_coreHttp->getRequestUri(true),
            'session_id'            => $this->_getSession()->getSessionId(),
            'http_referer'          => $this->_coreHttp->getHttpReferer(true),
        ));

        return $this;
    }

    /**
     * Return Online Minutes Interval
     *
     * @return int Minutes Interval
     */
    public function getOnlineMinutesInterval()
    {
        $configValue = $this->_coreStoreConfig->getConfig('customer/online_customers/online_minutes_interval');
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
     * @param   \Magento\Event\Observer $observer
     * @return  \Magento\Log\Model\Visitor
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
            $this->_eventManager->dispatch('visitor_init', array('visitor' => $this));
        }
        return $this;
    }

    /**
     * Saving visitor information by request
     *
     * Used in event "controller_action_postdispatch"
     *
     * @param   \Magento\Event\Observer $observer
     * @return  \Magento\Log\Model\Visitor
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
        } catch (\Exception $e) {
            $this->_logger->logException($e);
        }
        return $this;
    }

    /**
     * Bind customer data when customer login
     *
     * Used in event "customer_login"
     *
     * @param   \Magento\Event\Observer $observer
     * @return  \Magento\Log\Model\Visitor
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
     * @param   \Magento\Event\Observer $observer
     * @return  \Magento\Log\Model\Visitor
     */
    public function bindCustomerLogout($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if ($this->getCustomerId() && $customer) {
            $this->setDoCustomerLogout(true);
        }
        return $this;
    }

    /**
     * @param \Magento\Event\Observer $observer
     * @return $this
     */
    public function bindQuoteCreate($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if ($quote) {
            if ($quote->getIsCheckoutCart()) {
                $this->setQuoteId($quote->getId());
                $this->setDoQuoteCreate(true);
            }
        }
        return $this;
    }

    /**
     * @param \Magento\Event\Observer $observer
     * @return $this
     */
    public function bindQuoteDestroy($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if ($quote) {
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

    /**
     * @param object $data
     * @return $this
     */
    public function addCustomerData($data)
    {
        $customerId = $data->getCustomerId();
        if (intval($customerId) <= 0) {
            return $this;
        }
        $customerData = $this->_customerFactory->create()->load($customerId);
        $newCustomerData = array();
        foreach ($customerData->getData() as $propName => $propValue) {
            $newCustomerData['customer_' . $propName] = $propValue;
        }

        $data->addData($newCustomerData);
        return $this;
    }

    /**
     * @param object $data
     * @return $this
     */
    public function addQuoteData($data)
    {
        $quoteId = $data->getQuoteId();
        if (intval($quoteId) <= 0) {
            return $this;
        }
        $data->setQuoteData($this->_quoteFactory->create()->load($quoteId));
        return $this;
    }

    /**
     * @param \Magento\Event\Observer $observer
     * @return bool
     */
    public function isModuleIgnored($observer)
    {
        if (is_array($this->_ignores) && $observer) {
            $curModule = $observer->getEvent()->getControllerAction()->getRequest()->getRouteName();
            if (isset($this->_ignores[$curModule])) {
                return true;
            }
        }
        return false;
    }
}

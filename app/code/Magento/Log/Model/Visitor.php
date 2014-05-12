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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Log\Model;

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
 */
class Visitor extends \Magento\Framework\Model\AbstractModel
{
    const DEFAULT_ONLINE_MINUTES_INTERVAL = 15;

    const VISITOR_TYPE_CUSTOMER = 'c';

    const VISITOR_TYPE_VISITOR = 'v';

    /**
     * @var bool
     */
    protected $_skipRequestLogging = false;

    /**
     * @var string[]
     */
    protected $_ignoredUserAgents;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_coreConfig;

    /**
     * Ignored Modules
     *
     * @var array
     */
    protected $_ignores;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * @var \Magento\Sales\Model\QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    protected $_httpHeader;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remoteAddress;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\ServerAddress
     */
    protected $_serverAddress;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Sales\Model\QuoteFactory $quoteFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig
     * @param \Magento\Framework\HTTP\Header $httpHeader
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Framework\HTTP\PhpEnvironment\ServerAddress $serverAddress
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $ignoredUserAgents
     * @param array $ignores
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig,
        \Magento\Framework\HTTP\Header $httpHeader,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\HTTP\PhpEnvironment\ServerAddress $serverAddress,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $ignoredUserAgents = array(),
        array $ignores = array(),
        array $data = array()
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_quoteFactory = $quoteFactory;
        $this->_session = $session;
        $this->_storeManager = $storeManager;
        $this->_coreConfig = $coreConfig;
        $this->_ignoredUserAgents = $ignoredUserAgents;
        $this->_httpHeader = $httpHeader;
        $this->_remoteAddress = $remoteAddress;
        $this->_serverAddress = $serverAddress;
        $this->dateTime = $dateTime;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_ignores = $ignores;
    }

    /**
     * Object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Log\Model\Resource\Visitor');
        $userAgent = $this->_httpHeader->getHttpUserAgent();
        if ($this->_ignoredUserAgents) {
            if (in_array($userAgent, $this->_ignoredUserAgents)) {
                $this->_skipRequestLogging = true;
            }
        }
    }

    /**
     * Retrieve session object
     *
     * @return \Magento\Framework\Session\SessionManagerInterface
     */
    protected function _getSession()
    {
        return $this->_session;
    }

    /**
     * Skip request logging
     *
     * @param bool $skipRequestLogging
     * @return void
     */
    public function setSkipRequestLogging($skipRequestLogging)
    {
        $this->_skipRequestLogging = (bool)$skipRequestLogging;
    }

    /**
     * Initialize visitor information from server data
     *
     * @return $this
     */
    public function initServerData()
    {
        $clean = true;
        $this->addData(
            array(
                'server_addr' => $this->_serverAddress->getServerAddress(true),
                'remote_addr' => $this->_remoteAddress->getRemoteAddress(true),
                'http_secure' => $this->_storeManager->getStore()->isCurrentlySecure(),
                'http_host' => $this->_httpHeader->getHttpHost($clean),
                'http_user_agent' => $this->_httpHeader->getHttpUserAgent($clean),
                'http_accept_language' => $this->_httpHeader->getHttpAcceptLanguage($clean),
                'http_accept_charset' => $this->_httpHeader->getHttpAcceptCharset($clean),
                'request_uri' => $this->_httpHeader->getRequestUri($clean),
                'session_id' => $this->_getSession()->getSessionId(),
                'http_referer' => $this->_httpHeader->getHttpReferer($clean)
            )
        );

        return $this;
    }

    /**
     * Return Online Minutes Interval
     *
     * @return int Minutes Interval
     */
    public function getOnlineMinutesInterval()
    {
        $configValue = $this->_scopeConfig->getValue(
            'customer/online_customers/online_minutes_interval',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return intval($configValue) > 0 ? intval($configValue) : self::DEFAULT_ONLINE_MINUTES_INTERVAL;
    }

    /**
     * Retrieve url from model data
     *
     * @return string
     */
    public function getUrl()
    {
        $url = 'http' . ($this->getHttpSecure() ? 's' : '') . '://';
        $url .= $this->getHttpHost() . $this->getRequestUri();
        return $url;
    }

    /**
     * Return First Visit data in internal format.
     *
     * @return string
     */
    public function getFirstVisitAt()
    {
        if (!$this->hasData('first_visit_at')) {
            $this->setData('first_visit_at', $this->dateTime->now());
        }
        return $this->getData('first_visit_at');
    }

    /**
     * Return Last Visit data in internal format.
     *
     * @return string
     */
    public function getLastVisitAt()
    {
        if (!$this->hasData('last_visit_at')) {
            $this->setData('last_visit_at', $this->dateTime->now());
        }
        return $this->getData('last_visit_at');
    }

    /**
     * Initialization visitor information by request
     *
     * Used in event "controller_action_predispatch"
     *
     * @param   \Magento\Framework\Event\Observer $observer
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
            $this->setFirstVisitAt($this->dateTime->now());
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
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  \Magento\Log\Model\Visitor
     */
    public function saveByRequest($observer)
    {
        if ($this->_skipRequestLogging || $this->isModuleIgnored($observer)) {
            return $this;
        }

        try {
            $this->setLastVisitAt($this->dateTime->now());
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
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  \Magento\Log\Model\Visitor
     */
    public function bindCustomerLogin($observer)
    {
        /** @var \Magento\Customer\Service\V1\Data\Customer $customer */
        $customer = $observer->getEvent()->getCustomer();
        if (!$this->getCustomerId()) {
            $this->setDoCustomerLogin(true);
            $this->setCustomerId($customer->getId());
        }
    }

    /**
     * Bind customer data when customer logout
     *
     * Used in event "customer_logout"
     *
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  \Magento\Log\Model\Visitor
     */
    public function bindCustomerLogout($observer)
    {
        if ($this->getCustomerId()) {
            $this->setDoCustomerLogout(true);
        }
    }

    /**
     * Create binding of checkout quote
     *
     * @param \Magento\Framework\Event\Observer $observer
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
     * Destroy binding of checkout quote
     * @param \Magento\Framework\Event\Observer $observer
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
     *
     * @param array $data
     * @return $this
     */
    public function addIpData($data)
    {
        $ipData = array();
        $data->setIpData($ipData);
        return $this;
    }

    /**
     * Load quote data into $data
     *
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
     * Returns true if the module is required
     *
     * @param \Magento\Framework\Event\Observer $observer
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

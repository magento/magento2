<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request;
use Magento\Framework\App\RequestSafetyInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context as ModelContext;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Session\Config as SessionConfig;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Visitor responsible for initializing visitor's.
 *
 *  Used to track sessions of the logged in customers
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Visitor extends AbstractModel
{
    const VISITOR_TYPE_CUSTOMER = 'c';
    const VISITOR_TYPE_VISITOR = 'v';
    const DEFAULT_ONLINE_MINUTES_INTERVAL = 15;
    const XML_PATH_ONLINE_INTERVAL = 'customer/online_customers/online_minutes_interval';
    private const SECONDS_24_HOURS = 86400;

    /**
     * @var string[]
     */
    protected $ignoredUserAgents;

    /**
     * @var SessionManagerInterface
     */
    protected $session;

    /**
     * @var Header
     */
    protected $httpHeader;

    /**
     * @var bool
     */
    protected $skipRequestLogging = false;

    /**
     * Ignored Modules
     *
     * @var array
     */
    protected $ignores;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var RequestSafetyInterface
     */
    private $requestSafety;

    /**
     * @param ModelContext $context
     * @param Registry $registry
     * @param SessionManagerInterface $session
     * @param Header $httpHeader
     * @param ScopeConfigInterface $scopeConfig
     * @param DateTime $dateTime
     * @param IndexerRegistry $indexerRegistry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $ignoredUserAgents
     * @param array $ignores
     * @param array $data
     * @param RequestSafetyInterface|null $requestSafety
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ModelContext $context,
        Registry $registry,
        SessionManagerInterface $session,
        Header $httpHeader,
        ScopeConfigInterface $scopeConfig,
        DateTime $dateTime,
        IndexerRegistry $indexerRegistry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $ignoredUserAgents = [],
        array $ignores = [],
        array $data = [],
        RequestSafetyInterface $requestSafety = null
    ) {
        $this->session = $session;
        $this->httpHeader = $httpHeader;
        $this->ignoredUserAgents = $ignoredUserAgents;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->ignores = $ignores;
        $this->scopeConfig = $scopeConfig;
        $this->dateTime = $dateTime;
        $this->indexerRegistry = $indexerRegistry;
        $this->requestSafety = $requestSafety ?? ObjectManager::getInstance()->get(RequestSafetyInterface::class);
    }

    /**
     * Object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Visitor::class);
        $userAgent = $this->httpHeader->getHttpUserAgent();
        if ($this->ignoredUserAgents) {
            if (in_array($userAgent, $this->ignoredUserAgents)) {
                $this->skipRequestLogging = true;
            }
        }
    }

    /**
     * Skip request logging
     *
     * @param bool $skipRequestLogging
     * @return Visitor
     */
    public function setSkipRequestLogging($skipRequestLogging)
    {
        $this->skipRequestLogging = (bool)$skipRequestLogging;
        return $this;
    }

    /**
     * Initialization visitor by request. Used in event "controller_action_predispatch"
     *
     * @param EventObserver $observer
     * @return Visitor
     */
    public function initByRequest($observer)
    {
        if ($this->skipRequestLogging || $this->isModuleIgnored($observer)) {
            return $this;
        }

        if ($this->session->getVisitorData()) {
            $this->setData($this->session->getVisitorData());
            if ($this->getSessionId() != $this->session->getSessionId()) {
                $this->setSessionId($this->session->getSessionId());
            }
        }

        $this->setLastVisitAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));

        // prevent saving Visitor for safe methods, e.g. GET request
        if ($this->requestSafety->isSafeMethod()) {
            return $this;
        }

        if (!$this->getId()) {
            $this->setSessionId($this->session->getSessionId());
            $this->save();
            $this->_eventManager->dispatch('visitor_init', ['visitor' => $this]);
            $this->session->setVisitorData($this->getData());
        }
        return $this;
    }

    /**
     * Save visitor by request
     *
     * Used in event "controller_action_postdispatch"
     *
     * @param EventObserver $observer
     * @return Visitor
     */
    public function saveByRequest($observer)
    {
        // prevent saving Visitor for safe methods, e.g. GET request
        if (($this->skipRequestLogging || $this->requestSafety->isSafeMethod() || $this->isModuleIgnored($observer))
            && !$this->sessionIdHasChanged()
        ) {
            return $this;
        }

        try {
            if ($this->session->getSessionId() && $this->getSessionId() != $this->session->getSessionId()) {
                $this->setSessionId($this->session->getSessionId());
            }
            $this->save();
            $this->_eventManager->dispatch('visitor_activity_save', ['visitor' => $this]);
            $this->session->setVisitorData($this->getData());
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        return $this;
    }

    /**
     * Check if visitor session id was changed.
     *
     * @return bool
     */
    private function sessionIdHasChanged(): bool
    {
        $visitorData = $this->session->getVisitorData();
        $hasChanged = false;

        if (isset($visitorData['session_id'])) {
            $hasChanged = $this->session->getSessionId() !== $visitorData['session_id'];
        }

        return $hasChanged;
    }

    /**
     * Returns true if the module is required
     *
     * @param EventObserver $observer
     * @return bool
     */
    public function isModuleIgnored($observer)
    {
        if (is_array($this->ignores) && $observer) {
            $curModule = $this->requestSafety->getRouteName();
            if (isset($this->ignores[$curModule])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Bind customer data when customer login
     *
     * Used in event "customer_login"
     *
     * @param EventObserver $observer
     * @return Visitor
     */
    public function bindCustomerLogin($observer)
    {
        /** @var CustomerInterface $customer */
        $customer = $observer->getEvent()->getCustomer();
        if (!$this->getCustomerId()) {
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
     * @param EventObserver $observer
     * @return Visitor
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function bindCustomerLogout($observer)
    {
        if ($this->getCustomerId()) {
            $this->setDoCustomerLogout(true);
        }
        return $this;
    }

    /**
     * Create binding of checkout quote
     *
     * @param EventObserver $observer
     * @return Visitor
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
     *
     * @param EventObserver $observer
     * @return Visitor
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
     * Return clean time in seconds for visitor's outdated records
     *
     * @return string
     */
    public function getCleanTime()
    {
        return self::SECONDS_24_HOURS + $this->scopeConfig->getValue(
            SessionConfig::XML_PATH_COOKIE_LIFETIME,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Clean visitor's outdated records
     *
     * @return $this
     */
    public function clean()
    {
        $this->getResource()->clean($this);
        return $this;
    }

    /**
     * Retrieve Online Interval (in minutes)
     *
     * @return int Minutes Interval
     */
    public function getOnlineInterval()
    {
        $configValue = (int)$this->scopeConfig->getValue(
            static::XML_PATH_ONLINE_INTERVAL,
            ScopeInterface::SCOPE_STORE
        );
        return $configValue ?: static::DEFAULT_ONLINE_MINUTES_INTERVAL;
    }
}

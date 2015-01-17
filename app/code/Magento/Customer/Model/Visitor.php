<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

/**
 * Class Visitor
 * @package Magento\Customer\Model
 */
class Visitor extends \Magento\Framework\Model\AbstractModel
{
    const VISITOR_TYPE_CUSTOMER = 'c';

    const VISITOR_TYPE_VISITOR = 'v';

    /**
     * @var string[]
     */
    protected $ignoredUserAgents;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * @var \Magento\Framework\HTTP\Header
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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\HTTP\Header $httpHeader
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param array $ignoredUserAgents
     * @param array $ignores
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\HTTP\Header $httpHeader,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $ignoredUserAgents = [],
        array $ignores = [],
        array $data = []
    ) {
        $this->session = $session;
        $this->httpHeader = $httpHeader;
        $this->ignoredUserAgents = $ignoredUserAgents;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->ignores = $ignores;
        $this->scopeConfig = $scopeConfig;
        $this->dateTime = $dateTime;
    }

    /**
     * Object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Customer\Model\Resource\Visitor');
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
     * @return \Magento\Customer\Model\Visitor
     */
    public function setSkipRequestLogging($skipRequestLogging)
    {
        $this->skipRequestLogging = (bool)$skipRequestLogging;
        return $this;
    }

    /**
     * Initialization visitor by request
     *
     * Used in event "controller_action_predispatch"
     *
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  \Magento\Customer\Model\Visitor
     */
    public function initByRequest($observer)
    {
        if ($this->skipRequestLogging || $this->isModuleIgnored($observer)) {
            return $this;
        }
        if ($this->session->getVisitorData()) {
            $this->setData($this->session->getVisitorData());
        }
        if (!$this->getId()) {
            $this->setSessionId($this->session->getSessionId());
            $this->setLastVisitAt($this->dateTime->now());
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
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  \Magento\Customer\Model\Visitor
     */
    public function saveByRequest($observer)
    {
        if ($this->skipRequestLogging || $this->isModuleIgnored($observer)) {
            return $this;
        }

        try {
            $this->save();
            $this->_eventManager->dispatch('visitor_activity_save', ['visitor' => $this]);
            $this->session->setVisitorData($this->getData());
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
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
        if (is_array($this->ignores) && $observer) {
            $curModule = $observer->getEvent()->getControllerAction()->getRequest()->getRouteName();
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
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  \Magento\Customer\Model\Visitor
     */
    public function bindCustomerLogin($observer)
    {
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
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
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  \Magento\Customer\Model\Visitor
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
     * @param \Magento\Framework\Event\Observer $observer
     * @return  \Magento\Customer\Model\Visitor
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
     * @return  \Magento\Customer\Model\Visitor
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
        return $this->scopeConfig->getValue(
            \Magento\Framework\Session\Config::XML_PATH_COOKIE_LIFETIME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) + 86400;
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
}

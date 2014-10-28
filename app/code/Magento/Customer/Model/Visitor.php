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
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\HTTP\Header $httpHeader
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $ignoredUserAgents
     * @param array $ignores
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\HTTP\Header $httpHeader,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $ignoredUserAgents = array(),
        array $ignores = array(),
        $data = array()
    ) {
        $this->session = $session;
        $this->httpHeader = $httpHeader;
        $this->ignoredUserAgents = $ignoredUserAgents;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->ignores = $ignores;
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
            $this->save();
            $this->_eventManager->dispatch('visitor_init', array('visitor' => $this));
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
            $this->_eventManager->dispatch('visitor_activity_save', array('visitor' => $this));
            $this->session->setVisitorData($this->getData());
        } catch (\Exception $e) {
            $this->_logger->logException($e);
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
        /** @var \Magento\Customer\Service\V1\Data\Customer $customer */
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
}

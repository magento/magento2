<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Model\QuoteManager;
use Magento\Persistent\Model\QuoteResourceWrapper;
use Magento\Framework\App\ObjectManager;

/**
 * Observer of expired session
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CheckExpirePersistentQuoteObserver implements ObserverInterface
{
    /**
     * Customer session instance for managing customer authentication state
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Checkout session instance for managing quote data during checkout
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * Helper that provides persistent session functionality
     *
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSession = null;

    /**
     * @var \Magento\Persistent\Model\QuoteManager
     */
    protected $quoteManager;

    /**
     * Helper that provides configuration and utility methods for persistent functionality
     *
     * @var \Magento\Persistent\Helper\Data
     */
    protected $_persistentData = null;

    /**
     * Current HTTP request object
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * Checkout Page path
     *
     * @var string
     */
    private $checkoutPagePath = 'checkout';

    /**
     * Resource wrapper for efficient quote operations
     *
     * @var QuoteResourceWrapper|null
     */
    private ?QuoteResourceWrapper $quoteResourceWrapper;

    /**
     * Constructor
     *
     * @param Session $persistentSession
     * @param Data $persistentData
     * @param QuoteManager $quoteManager
     * @param ManagerInterface $eventManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param RequestInterface $request
     * @param QuoteResourceWrapper|null $quoteResourceWrapper
     */
    public function __construct(
        Session                         $persistentSession,
        \Magento\Persistent\Helper\Data $persistentData,
        QuoteManager                    $quoteManager,
        ManagerInterface                $eventManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        RequestInterface                $request,
        ?QuoteResourceWrapper           $quoteResourceWrapper = null
    ) {
        $this->_persistentSession = $persistentSession;
        $this->quoteManager = $quoteManager;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_eventManager = $eventManager;
        $this->_persistentData = $persistentData;
        $this->request = $request;
        $this->quoteResourceWrapper = $quoteResourceWrapper ?: ObjectManager::getInstance()
            ->get(QuoteResourceWrapper::class);
    }

    /**
     * Check and clear session data if persistent session expired
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->_persistentData->canProcess($observer)) {
            return;
        }

        //clear persistent when persistent data is disabled
        if ($this->isPersistentQuoteOutdated()) {
            $this->_eventManager->dispatch('persistent_session_expired');
            $this->quoteManager->expire();
            $this->_checkoutSession->clearQuote();
            $this->_customerSession->setCustomerId(null)->setCustomerGroupId(null);
            return;
        }

        if ($this->_persistentData->isEnabled() &&
            !$this->_persistentSession->isPersistent() &&
            !$this->_customerSession->isLoggedIn() &&
            $this->_checkoutSession->getQuoteId() &&
            // persistent session does not expire on onepage checkout page
            !$this->isRequestFromCheckoutPage($this->request) &&
            (bool)$this->quoteResourceWrapper->isPersistent($this->_checkoutSession->getQuoteId())
        ) {
            $this->_eventManager->dispatch('persistent_session_expired');
            $this->quoteManager->expire();
            $this->_customerSession->setCustomerId(null)->setCustomerGroupId(null);
        }
    }

    /**
     * Checks if current quote marked as persistent and Persistence Functionality is disabled.
     *
     * @return bool
     */
    private function isPersistentQuoteOutdated(): bool
    {
        if (!($this->_persistentData->isEnabled() && $this->_persistentData->isShoppingCartPersist())
            && !$this->_customerSession->isLoggedIn()
            && $this->_checkoutSession->getQuoteId()
            && $this->quoteResourceWrapper->isActive($this->_checkoutSession->getQuoteId())
        ) {
            return (bool)$this->quoteResourceWrapper->isPersistent($this->_checkoutSession->getQuoteId());
        }
        return false;
    }

    /**
     * Check current request is coming from onepage checkout page.
     *
     * @param RequestInterface $request
     * @return bool
     */
    private function isRequestFromCheckoutPage(RequestInterface $request): bool
    {
        $requestUri = (string)$request->getRequestUri();
        $refererUri = (string)$request->getServer('HTTP_REFERER');

        /** @var bool $isCheckoutPage */
        $isCheckoutPage = (
            false !== strpos($requestUri, (string) $this->checkoutPagePath) ||
            false !== strpos($refererUri, (string) $this->checkoutPagePath)
        );

        return $isCheckoutPage;
    }
}

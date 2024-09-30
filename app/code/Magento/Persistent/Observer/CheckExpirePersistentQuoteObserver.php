<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;

/**
 * Observer of expired session
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CheckExpirePersistentQuoteObserver implements ObserverInterface
{
    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Checkout session
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
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSession = null;

    /**
     * @var \Magento\Persistent\Model\QuoteManager
     */
    protected $quoteManager;

    /**
     * Persistent data
     *
     * @var \Magento\Persistent\Helper\Data
     */
    protected $_persistentData = null;

    /**
     * Request
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
     * @var Quote
     */
    private $quote;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Persistent\Model\QuoteManager $quoteManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\RequestInterface $request
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        \Magento\Persistent\Helper\Session $persistentSession,
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Persistent\Model\QuoteManager $quoteManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\RequestInterface $request,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->_persistentSession = $persistentSession;
        $this->quoteManager = $quoteManager;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_eventManager = $eventManager;
        $this->_persistentData = $persistentData;
        $this->request = $request;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Check and clear session data if persistent session expired
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
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
            $this->getQuote()->getIsPersistent()
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function isPersistentQuoteOutdated(): bool
    {
        if (!($this->_persistentData->isEnabled() && $this->_persistentData->isShoppingCartPersist())
            && !$this->_customerSession->isLoggedIn()
            && $this->_checkoutSession->getQuoteId()
            && $this->isActiveQuote()
        ) {
            return (bool)$this->getQuote()->getIsPersistent();
        }
        return false;
    }

    /**
     * Getter for Quote with micro optimization
     *
     * @return Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getQuote(): Quote
    {
        if ($this->quote === null) {
            $this->quote = $this->_checkoutSession->getQuote();
        }
        return $this->quote;
    }

    /**
     * Check if quote is active.
     *
     * @return bool
     */
    private function isActiveQuote(): bool
    {
        try {
            $this->quoteRepository->getActive($this->_checkoutSession->getQuoteId());
            return true;
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * Check current request is coming from onepage checkout page.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    private function isRequestFromCheckoutPage(\Magento\Framework\App\RequestInterface $request): bool
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

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
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;

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
     * Path identifier for checkout pages
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
     * Quote repository kept for backward compatibility of the constructor signature
     *
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $quoteRepository;

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
     * @param CartRepositoryInterface $quoteRepository
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
        CartRepositoryInterface         $quoteRepository,
        ?QuoteResourceWrapper           $quoteResourceWrapper = null
    ) {
        $this->_persistentSession = $persistentSession;
        $this->quoteManager = $quoteManager;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_eventManager = $eventManager;
        $this->_persistentData = $persistentData;
        $this->request = $request;
        $this->quoteRepository = $quoteRepository;
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
            $this->getQuoteFlags()['is_persistent']
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
        ) {
            $flags = $this->getQuoteFlags();
            return $flags['is_active'] && $flags['is_persistent'];
        }
        return false;
    }

    /**
     * Resolve the is_active and is_persistent flags, preferring the loaded quote and reading both in one DB call.
     *
     * @return array{is_active: bool, is_persistent: bool}
     */
    private function getQuoteFlags(): array
    {
        if ($this->_checkoutSession->hasQuote()) {
            try {
                $quote = $this->_checkoutSession->getQuote();
                return [
                    'is_active' => (bool)$quote->getIsActive(),
                    'is_persistent' => (bool)$quote->getIsPersistent(),
                ];
            } catch (LocalizedException $e) {
                return $this->quoteResourceWrapper->getFlags($this->_checkoutSession->getQuoteId());
            }
        }

        return $this->quoteResourceWrapper->getFlags($this->_checkoutSession->getQuoteId());
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

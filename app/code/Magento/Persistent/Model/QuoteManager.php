<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Model;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Persistent\Helper\Data;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor;

/**
 * Quote manager model
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class QuoteManager
{
    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     */
    protected $persistentSession;

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Persistent data
     *
     * @var Data
     */
    protected $persistentData;

    /**
     * Whether set quote to be persistent in workflow
     *
     * @var bool
     */
    protected $_setQuotePersistent = true;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * @var ShippingAssignmentProcessor
     */
    private $shippingAssignmentProcessor;
    /**
     * @var CartExtensionFactory
     */
    private $cartExtensionFactory;

    /**
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param Data $persistentData
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     * @param CartExtensionFactory|null $cartExtensionFactory
     * @param ShippingAssignmentProcessor|null $shippingAssignmentProcessor
     */
    public function __construct(
        \Magento\Persistent\Helper\Session $persistentSession,
        Data $persistentData,
        \Magento\Checkout\Model\Session $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        ?CartExtensionFactory $cartExtensionFactory = null,
        ?ShippingAssignmentProcessor $shippingAssignmentProcessor = null
    ) {
        $this->persistentSession = $persistentSession;
        $this->persistentData = $persistentData;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->cartExtensionFactory = $cartExtensionFactory
            ?? ObjectManager::getInstance()->get(CartExtensionFactory::class);
        $this->shippingAssignmentProcessor = $shippingAssignmentProcessor
            ?? ObjectManager::getInstance()->get(ShippingAssignmentProcessor::class);
    }

    /**
     * Clear cart of customer data if exists and reset guest information, remove persistent session
     *
     * @param bool $checkQuote Check quote to be persistent (not stolen)
     * @return void
     */
    public function setGuest($checkQuote = false)
    {
        /** @var $quote Quote */
        $quote = $this->checkoutSession->getQuote();
        if ($quote && $quote->getId()) {
            if ($checkQuote && !$this->persistentData->isShoppingCartPersist() && !$quote->getIsPersistent()) {
                $this->checkoutSession->clearQuote()->clearStorage();
                return;
            }

            $quote->getPaymentsCollection()->walk('delete');
            $quote->getAddressesCollection()->walk('delete');
            $this->_setQuotePersistent = false;
            $quote->setIsActive(true)
                ->setCustomerId(null)
                ->setCustomerEmail(null)
                ->setCustomerFirstname(null)
                ->setCustomerLastname(null)
                ->setCustomerGroupId(GroupInterface::NOT_LOGGED_IN_ID)
                ->setIsPersistent(false)
                ->removeAllAddresses();
            //Create guest addresses
            $quote->getShippingAddress();
            $quote->getBillingAddress();
            $this->setShippingAssignments($quote);
            $quote->collectTotals();
            $this->quoteRepository->save($quote);
        }

        $this->persistentSession->getSession()->removePersistentCookie();
        $this->persistentSession->setSession(null);
    }

    /**
     * Emulate guest cart with persistent cart
     *
     * Converts persistent cart tied to logged out customer to a guest cart, retaining customer information required for
     * checkout
     *
     * @return void
     */
    public function convertCustomerCartToGuest()
    {
        $quoteId = $this->checkoutSession->getQuoteId();
        /** @var $quote Quote */
        $quote = $this->quoteRepository->get($quoteId);
        if ($quote && $quote->getId()) {
            $this->_setQuotePersistent = false;
            $quote->setIsActive(true)
                ->setCustomerId(null)
                ->setCustomerEmail(null)
                ->setCustomerFirstname(null)
                ->setCustomerLastname(null)
                ->setIsPersistent(false);
            $quote->getAddressesCollection()->walk('setCustomerAddressId', ['customerAddressId' => null]);
            $quote->getAddressesCollection()->walk('setCustomerId', ['customerId' => null]);
            $quote->getAddressesCollection()->walk('setEmail', ['email' => null]);
            $quote->collectTotals();
            $this->persistentSession->getSession()->removePersistentCookie();
            $this->persistentSession->setSession(null);
            $this->quoteRepository->save($quote);
        }
    }

    /**
     * Expire persistent quote
     *
     * @return void
     */
    public function expire()
    {
        $quote = $this->checkoutSession->setLoadInactive()->getQuote();
        if ($quote->getIsActive() && $quote->getCustomerId()) {
            $this->checkoutSession->setCustomerData(null)->clearQuote()->clearStorage();
        } else {
            $quote->setIsActive(true)
                ->setIsPersistent(false)
                ->setCustomerId(null)
                ->setCustomerGroupId(GroupInterface::NOT_LOGGED_IN_ID);
        }
    }

    /**
     * Is quote persistent
     *
     * @return bool
     */
    public function isPersistent()
    {
        return $this->_setQuotePersistent;
    }

    /**
     * Create shipping assignment for shopping cart
     *
     * @param CartInterface $quote
     */
    private function setShippingAssignments(CartInterface $quote): void
    {
        $shippingAssignments = [];
        if (!$quote->isVirtual() && $quote->getItemsQty() > 0) {
            $shippingAssignments[] = $this->shippingAssignmentProcessor->create($quote);
        }
        $cartExtension = $quote->getExtensionAttributes();
        if ($cartExtension === null) {
            $cartExtension = $this->cartExtensionFactory->create();
        }
        $cartExtension->setShippingAssignments($shippingAssignments);
        $quote->setExtensionAttributes($cartExtension);
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Model;

/**
 * Class QuoteManager
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
     * @var \Magento\Persistent\Helper\Data
     */
    protected $persistentData;

    /**
     * Whether set quote to be persistent in workflow
     *
     * @var bool
     */
    protected $_setQuotePersistent = true;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        \Magento\Persistent\Helper\Session $persistentSession,
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->persistentSession = $persistentSession;
        $this->persistentData = $persistentData;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Clear cart of customer data if exists and reset guest information, remove persistent session
     *
     * @param bool $checkQuote Check quote to be persistent (not stolen)
     * @return void
     */
    public function setGuest($checkQuote = false)
    {
        /** @var $quote \Magento\Quote\Model\Quote */
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
                ->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID)
                ->setIsPersistent(false)
                ->removeAllAddresses();
            //Create guest addresses
            $quote->getShippingAddress();
            $quote->getBillingAddress();
            $quote->collectTotals();
            $this->quoteRepository->save($quote);
        }

        $this->persistentSession->getSession()->removePersistentCookie();
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
        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $this->quoteRepository->get($this->checkoutSession->getQuote()->getId());
        if ($quote && $quote->getId()) {
            $this->_setQuotePersistent = false;
            $quote->setIsActive(true)
                ->setCustomerId(null)
                ->setCustomerEmail(null)
                ->setCustomerFirstname(null)
                ->setCustomerLastname(null)
                ->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID)
                ->setIsPersistent(false);
            $quote->getAddressesCollection()->walk('setCustomerAddressId', ['customerAddressId' => null]);
            $quote->getAddressesCollection()->walk('setCustomerId', ['customerId' => null]);
            $quote->getAddressesCollection()->walk('setEmail', ['email' => null]);
            $quote->collectTotals();
            $this->persistentSession->getSession()->removePersistentCookie();
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
                ->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);
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
}

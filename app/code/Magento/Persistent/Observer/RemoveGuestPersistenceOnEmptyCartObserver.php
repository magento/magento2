<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Observer to remove persistent session if guest empties persistent cart previously created and added to by customer.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class RemoveGuestPersistenceOnEmptyCartObserver implements ObserverInterface
{
    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     */
    private $persistenceSessionHelper;

    /**
     * Quote manager
     *
     * @var \Magento\Persistent\Model\QuoteManager
     */
    private $quoteManager;

    /**
     * Persistent Data
     *
     * @var \Magento\Persistent\Helper\Data
     */
    private $persistenceDataHelper;

    /**
     * Cart Repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     */
    private $cartRepository;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Persistent\Helper\Session $persistenceSessionHelper
     * @param \Magento\Persistent\Helper\Data $persistenceDataHelper
     * @param \Magento\Persistent\Model\QuoteManager $quoteManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     */
    public function __construct(
        \Magento\Persistent\Helper\Session $persistenceSessionHelper,
        \Magento\Persistent\Helper\Data $persistenceDataHelper,
        \Magento\Persistent\Model\QuoteManager $quoteManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository
    ) {
        $this->persistenceSessionHelper = $persistenceSessionHelper;
        $this->customerSession = $customerSession;
        $this->quoteManager = $quoteManager;
        $this->persistenceDataHelper = $persistenceDataHelper;
        $this->cartRepository = $cartRepository;
    }

    /**
     * Set persistent session to guest if cart has been emptied and customer not logged in
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->persistenceSessionHelper->isPersistent()
            || $this->customerSession->isLoggedIn()
            || !$this->persistenceDataHelper->isShoppingCartPersist()
        ) {
            return;
        }

        try {
            $custId = $this->persistenceSessionHelper->getSession()->getCustomerId();
            /** @var \Magento\Quote\Api\Data\CartInterface $cart */
            $cart = $this->cartRepository->getActiveForCustomer($custId);
        } catch (NoSuchEntityException $entityException) {
            $cart = null;
        }

        if (!$cart || $cart->getItemsCount() == 0) {
            $this->customerSession->setCustomerId(null)
                ->setCustomerGroupId(null);
            $this->quoteManager->setGuest();
        }
    }
}

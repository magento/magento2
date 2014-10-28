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

namespace Magento\Checkout\Service\V1\Cart;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\StateException;
use Magento\Authorization\Model\UserContextInterface;

/** 
 * Cart write service object. 
 */
class WriteService implements WriteServiceInterface
{
    /**
     * Quote factory.
     *
     * @var \Magento\Sales\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * Quote repository.
     *
     * @var \Magento\Sales\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * Store manager interface.
     *
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Customer registry.
     *
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * User context interface.
     *
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * Quote factory.
     *
     * @var \Magento\Sales\Model\Service\QuoteFactory
     */
    protected $quoteServiceFactory;

    /**
     * Constructs a cart write service object.
     *
     * @param \Magento\Sales\Model\QuoteFactory $quoteFactory Quote factory.
     * @param \Magento\Sales\Model\QuoteRepository $quoteRepository Quote repository.
     * @param \Magento\Framework\StoreManagerInterface $storeManager Store manager.
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry Customer registry.
     * @param UserContextInterface $userContext User context.
     * @param \Magento\Sales\Model\Service\QuoteFactory $quoteServiceFactory Quote service factory.
     */
    public function __construct(
        \Magento\Sales\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\QuoteRepository $quoteRepository,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        UserContextInterface $userContext,
        \Magento\Sales\Model\Service\QuoteFactory $quoteServiceFactory
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->quoteRepository = $quoteRepository;
        $this->storeManager = $storeManager;
        $this->customerRegistry = $customerRegistry;
        $this->userContext = $userContext;
        $this->quoteServiceFactory = $quoteServiceFactory;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException The empty cart and quote could not be created.
     * @return int Cart ID.
     */
    public function create()
    {
        $quote = $this->userContext->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER
            ? $this->createCustomerCart()
            : $this->createAnonymousCart();

        try {
            $quote->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Cannot create quote');
        }
        return $quote->getId();
    }

    /**
     * Creates an anonymous cart.
     *
     * @return \Magento\Sales\Model\Quote Cart object.
     */
    protected function createAnonymousCart()
    {
        $storeId = $this->storeManager->getStore()->getId();
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteFactory->create();
        $quote->setStoreId($storeId);
        return $quote;
    }

    /**
     * Creates a cart for the currently logged-in customer.
     *
     * @return \Magento\Sales\Model\Quote Cart object.
     * @throws CouldNotSaveException The cart could not be created.
     */
    protected function createCustomerCart()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $customer = $this->customerRegistry->retrieve($this->userContext->getUserId());

        $currentCustomerQuote = $this->quoteFactory->create()->loadByCustomer($customer);
        if ($currentCustomerQuote->getId() && $currentCustomerQuote->getIsActive()) {
            throw new CouldNotSaveException('Cannot create quote');
        }

        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteFactory->create();
        $quote->setStoreId($storeId);
        $quote->setCustomer($customer);
        $quote->setCustomerIsGuest(0);
        return $quote;
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The cart ID.
     * @param int $customerId The customer ID.
     * @return boolean
     * @throws \Magento\Framework\Exception\StateException The customer cannot be assigned to the specified cart: The cart belongs to a different store or is not anonymous, or the customer already has an active cart.
     */
    public function assignCustomer($cartId, $customerId)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $quote = $this->quoteRepository->get($cartId);
        $customer = $this->customerRegistry->retrieve($customerId);
        if (!in_array($storeId, $customer->getSharedStoreIds())) {
            throw new StateException('Cannot assign customer to the given cart. The cart belongs to different store.');
        }
        if ($quote->getCustomerId()) {
            throw new StateException('Cannot assign customer to the given cart. The cart is not anonymous.');
        }
        $currentCustomerQuote = $this->quoteFactory->create()->loadByCustomer($customer);
        if ($currentCustomerQuote->getId()) {
            throw new StateException('Cannot assign customer to the given cart. Customer already has active cart.');
        }

        $quote->setCustomer($customer);
        $quote->setCustomerIsGuest(0);
        $quote->save();
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The cart ID.
     * @return int Order ID.
     */
    public function order($cartId)
    {
        $quote = $this->quoteRepository->get($cartId);
        /** @var \Magento\Sales\Model\Service\Quote $quoteService */
        $quoteService = $this->quoteServiceFactory->create(['quote' => $quote]);
        $order = $quoteService->submitOrderWithDataObject();
        return $order->getId();
    }
}

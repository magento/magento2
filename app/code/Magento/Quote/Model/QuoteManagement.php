<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

use Magento\Quote\Model\Quote as QuoteEntity;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Api\Data\OrderDataBuilder as OrderBuilder;
use Magento\Sales\Api\OrderManagementInterface as OrderManagement;
use Magento\Quote\Model\Quote\Address\ToOrder as ToOrderConverter;
use Magento\Quote\Model\Quote\Address\ToOrderAddress as ToOrderAddressConverter;
use Magento\Quote\Model\Quote\Item\ToOrderItem as ToOrderItemConverter;
use Magento\Quote\Model\Quote\Payment\ToOrderPayment as ToOrderPaymentConverter;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\StateException;

/**
 * Class QuoteManagement
 */
class QuoteManagement implements \Magento\Quote\Api\CartManagementInterface
{
    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var QuoteValidator
     */
    protected $quoteValidator;

    /**
     * @var OrderBuilder|\Magento\Framework\Api\Builder
     */
    protected $orderBuilder;

    /**
     * @var OrderManagement
     */
    protected $orderManagement;

    /**
     * @var CustomerManagement
     */
    protected $customerManagement;

    /**
     * @var ToOrderConverter
     */
    protected $quoteAddressToOrder;

    /**
     * @var ToOrderAddressConverter
     */
    protected $quoteAddressToOrderAddress;

    /**
     * @var ToOrderItemConverter
     */
    protected $quoteItemToOrderItem;

    /**
     * @var ToOrderPaymentConverter
     */
    protected $quotePaymentToOrderPayment;

    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerModelFactory;

    /**
     * @param EventManager $eventManager
     * @param QuoteValidator $quoteValidator
     * @param OrderBuilder $orderBuilder
     * @param OrderManagement $orderManagement
     * @param CustomerManagement $customerManagement
     * @param ToOrderConverter $quoteAddressToOrder
     * @param ToOrderAddressConverter $quoteAddressToOrderAddress
     * @param ToOrderItemConverter $quoteItemToOrderItem
     * @param ToOrderPaymentConverter $quotePaymentToOrderPayment
     * @param UserContextInterface $userContext
     * @param QuoteRepository $quoteRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\CustomerFactory $customerModelFactory
     */
    public function __construct(
        EventManager $eventManager,
        QuoteValidator $quoteValidator,
        OrderBuilder $orderBuilder,
        OrderManagement $orderManagement,
        CustomerManagement $customerManagement,
        ToOrderConverter $quoteAddressToOrder,
        ToOrderAddressConverter $quoteAddressToOrderAddress,
        ToOrderItemConverter $quoteItemToOrderItem,
        ToOrderPaymentConverter $quotePaymentToOrderPayment,
        UserContextInterface $userContext,
        QuoteRepository $quoteRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\CustomerFactory $customerModelFactory
    ) {
        $this->eventManager = $eventManager;
        $this->quoteValidator = $quoteValidator;
        $this->orderBuilder = $orderBuilder;
        $this->orderManagement = $orderManagement;
        $this->customerManagement = $customerManagement;
        $this->quoteAddressToOrder = $quoteAddressToOrder;
        $this->quoteAddressToOrderAddress = $quoteAddressToOrderAddress;
        $this->quoteItemToOrderItem = $quoteItemToOrderItem;
        $this->quotePaymentToOrderPayment = $quotePaymentToOrderPayment;
        $this->userContext = $userContext;
        $this->quoteRepository = $quoteRepository;
        $this->customerRepository = $customerRepository;
        $this->customerModelFactory = $customerModelFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function createEmptyCart($storeId)
    {
        $quote = $this->userContext->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER
            ? $this->createCustomerCart($storeId)
            : $this->createAnonymousCart($storeId);

        try {
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Cannot create quote');
        }
        return $quote->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function assignCustomer($cartId, $customerId, $storeId)
    {
        $quote = $this->quoteRepository->getActive($cartId);
        $customer = $this->customerRepository->getById($customerId);
        $customerModel = $this->customerModelFactory->create();

        if (!in_array($storeId, $customerModel->load($customerId)->getSharedStoreIds())) {
            throw new StateException('Cannot assign customer to the given cart. The cart belongs to different store.');
        }
        if ($quote->getCustomerId()) {
            throw new StateException('Cannot assign customer to the given cart. The cart is not anonymous.');
        }
        try {
            $this->quoteRepository->getForCustomer($customerId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw new StateException('Cannot assign customer to the given cart. Customer already has active cart.');
        }

        $quote->setCustomer($customer);
        $quote->setCustomerIsGuest(0);
        $this->quoteRepository->save($quote);
        return true;

    }

    /**
     * Creates an anonymous cart.
     *
     * @param int $storeId
     * @return \Magento\Quote\Model\Quote Cart object.
     */
    protected function createAnonymousCart($storeId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->create();
        $quote->setStoreId($storeId);
        return $quote;
    }

    /**
     * Creates a cart for the currently logged-in customer.
     *
     * @param int $storeId
     * @return \Magento\Quote\Model\Quote Cart object.
     * @throws CouldNotSaveException The cart could not be created.
     */
    protected function createCustomerCart($storeId)
    {
        $customer = $this->customerRepository->getById($this->userContext->getUserId());

        try {
            $this->quoteRepository->getActiveForCustomer($this->userContext->getUserId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw new CouldNotSaveException('Cannot create quote');
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->create();
        $quote->setStoreId($storeId);
        $quote->setCustomer($customer);
        $quote->setCustomerIsGuest(0);
        return $quote;
    }

    /**
     * {@inheritdoc}
     */
    public function placeOrder($cartId)
    {
        $quote = $this->quoteRepository->getActive($cartId);
        $order = $this->submit($quote);
        return $order->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getCartForCustomer($customerId)
    {
        return $this->quoteRepository->getActiveForCustomer($customerId);
    }

    /**
     * Delete quote item
     *
     * @param Quote $quote
     * @param array $orderData
     * @return \Magento\Framework\Model\AbstractExtensibleModel|\Magento\Sales\Api\Data\OrderInterface|object|void
     * @throws \Exception
     * @throws \Magento\Framework\Model\Exception
     */
    public function submit(QuoteEntity $quote, $orderData = [])
    {
        if (!$quote->getAllVisibleItems()) {
            $quote->setIsActive(false);
            return;
        }

        return $this->submitQuote($quote, $orderData);
    }

    /**
     * @param Quote $quote
     * @return array
     */
    protected function resolveItems(QuoteEntity $quote)
    {
        $quoteItems = $quote->getAllItems();
        for ($i = 0; $i < count($quoteItems) - 1; $i++) {
            for ($j = 0; $j < count($quoteItems) - $i - 1; $j++) {
                if ($quoteItems[$i]->getParentItemId() == $quoteItems[$j]->getId()) {
                    $tempItem = $quoteItems[$i];
                    $quoteItems[$i] = $quoteItems[$j];
                    $quoteItems[$j] = $tempItem;
                }
            }
        }
        $orderItems = [];
        foreach ($quoteItems as $quoteItem) {
            $parentItem = (isset($orderItems[$quoteItem->getParentItemId()])) ?
                $orderItems[$quoteItem->getParentItemId()] : null;
            $orderItems[$quoteItem->getId()] =
                $this->quoteItemToOrderItem->convert($quoteItem, ['parent_item' => $parentItem]);
        }
        return array_values($orderItems);
    }

    /**
     * Submit quote
     *
     * @param Quote $quote
     * @param array $orderData
     * @return \Magento\Framework\Model\AbstractExtensibleModel|\Magento\Sales\Api\Data\OrderInterface|object
     * @throws \Exception
     * @throws \Magento\Framework\Model\Exception
     */
    protected function submitQuote(QuoteEntity $quote, $orderData = [])
    {
        $this->quoteValidator->validateBeforeSubmit($quote);
        if (!$quote->getCustomerIsGuest()) {
            $this->customerManagement->populateCustomerInfo($quote);
        }
        $addresses = [];
        if ($quote->isVirtual()) {
            $this->orderBuilder->populate(
                $this->quoteAddressToOrder->convert($quote->getBillingAddress(), $orderData)
            );
        } else {
            $this->orderBuilder->populate(
                $this->quoteAddressToOrder->convert($quote->getShippingAddress(), $orderData)
            );
            $shippingAddress = $this->quoteAddressToOrderAddress->convert(
                $quote->getShippingAddress(),
                [
                    'address_type' => 'shipping',
                    'email' => $quote->getCustomerEmail()
                ]
            );
            $addresses[] = $shippingAddress;
            $this->orderBuilder->setShippingAddress($shippingAddress);

        }
        $billingAddress = $this->quoteAddressToOrderAddress->convert(
            $quote->getBillingAddress(),
            [
                'address_type' => 'billing',
                'email' => $quote->getCustomerEmail()
            ]
        );
        $addresses[] = $billingAddress;
        $this->orderBuilder->setBillingAddress($billingAddress);
        $this->orderBuilder->setAddresses($addresses);
        $this->orderBuilder->setPayments(
            [$this->quotePaymentToOrderPayment->convert($quote->getPayment())]
        );
        $this->orderBuilder->setItems($this->resolveItems($quote));
        if ($quote->getCustomer()) {
            $this->orderBuilder->setCustomerId($quote->getCustomer()->getId());
        }
        $this->orderBuilder->setQuoteId($quote->getId());
        $this->orderBuilder->setCustomerEmail($quote->getCustomerEmail());
        $this->orderBuilder->setCustomerFirstname($quote->getCustomerFirstname());
        $this->orderBuilder->setCustomerMiddlename($quote->getCustomerMiddlename());
        $this->orderBuilder->setCustomerLastname($quote->getCustomerLastname());
        $order = $this->orderBuilder->create();
        $this->eventManager->dispatch(
            'sales_model_service_quote_submit_before',
            [
                'order' => $order,
                'quote' => $quote
            ]
        );
        try {
            $order = $this->orderManagement->place($order);
            $quote->setIsActive(false);
            $this->eventManager->dispatch(
                'sales_model_service_quote_submit_success',
                [
                    'order' => $order,
                    'quote' => $quote
                ]
            );
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            $this->eventManager->dispatch(
                'sales_model_service_quote_submit_failure',
                [
                    'order' => $order,
                    'quote' => $quote
                ]
            );
            throw $e;
        }
        return $order;
    }
}
